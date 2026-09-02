#!/bin/sh
# Presence Platform — Marketplace Storefront: Vercel container entrypoint.
#
# Vercel-specific variant of docker/entrypoint.sh. Differences from the
# Render entrypoint:
#
#   1. Writable runtime state is relocated to /tmp (the only location
#      guaranteed writable on Vercel's ephemeral container filesystem).
#      Laravel's storage/ tree is symlinked there; the SQLite database
#      file lives there too. Cold restart loses everything in /tmp — see
#      .agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md.
#
#   2. Apache's listen port is patched from $PORT at startup (Vercel
#      injects PORT; Apache's Listen directive cannot read env vars at
#      parse time, so the entrypoint substitutes the placeholder in
#      vhost.vercel.conf).
#
#   3. The chown step is skipped when the entrypoint is not running as
#      root — Vercel may invoke the container as a non-root user.
#
# Everything else mirrors docker/entrypoint.sh per ADR-004 (DB key bootstrap,
# materialize DB_DATABASE into .env per OBS-005, idempotent migrate).

set -eu

# Diagnostics: trace every command to stderr so Vercel's build/runtime
# logs show exactly what the entrypoint did, in what order, and where it
# failed (if it fails). Without this, runtime errors like
# "DatabaseManager.php line 226" are opaque — we can't tell whether the
# entrypoint's .env materialization, storage symlink, or migrate step
# succeeded.
set -x

APP_DIR="${APP_DIR:-/var/www/html}"
cd "$APP_DIR"

echo "=== Vercel entrypoint START ===" >&2
echo "APP_DIR=$APP_DIR" >&2
echo "EPHEMERAL_ROOT=${EPHEMERAL_ROOT:-/tmp/storefront}" >&2
echo "PORT=${PORT:-<unset, will default to 8080>}" >&2
echo "DB_DATABASE=${DB_DATABASE:-<unset>}" >&2
echo "id=$(id)" >&2

# ---------------------------------------------------------------------------
# 1. Writable runtime directories — relocated to /tmp because Vercel's
#    container filesystem is ephemeral. /var/www/html itself may be
#    read-only in some Vercel configurations; /tmp is the canonical
#    writable scratch space.
# ---------------------------------------------------------------------------
EPHEMERAL_ROOT="${EPHEMERAL_ROOT:-/tmp/storefront}"
STORAGE_DIR="$EPHEMERAL_ROOT/storage"

mkdir -p \
    "$STORAGE_DIR/framework/cache/data" \
    "$STORAGE_DIR/framework/sessions" \
    "$STORAGE_DIR/framework/views" \
    "$STORAGE_DIR/logs" \
    bootstrap/cache

# Replace the shipped storage/ with a symlink to the ephemeral tree so
# Laravel's compiled views, sessions, cache, and logs land somewhere
# writable. The image-shipped storage/ tree is preserved in git via
# .gitignore placeholder files only; nothing of value is destroyed here.
#
# Non-fatal: if /var/www/html is read-only (can't rm/ln), log a warning and
# continue — the app may still work if the shipped storage/ is writable,
# or it may crash with a clearer error that points to storage/ writability
# rather than the opaque DatabaseManager error.
if [ ! -L storage ]; then
    if rm -rf storage 2>/dev/null && ln -s "$STORAGE_DIR" storage 2>/dev/null; then
        echo "storage/ symlinked to $STORAGE_DIR" >&2
    else
        echo "WARNING: could not symlink storage/ (read-only filesystem?). " >&2
        echo "  Trying subdirectory-level symlinks as fallback..." >&2
        for sub in framework/views framework/sessions framework/cache/data logs; do
            rm -rf "storage/$sub" 2>/dev/null || true
            ln -s "$STORAGE_DIR/$sub" "storage/$sub" 2>/dev/null || true
        done
        echo "  Fallback complete (some subdirs may not be symlinked)" >&2
    fi
fi

# ---------------------------------------------------------------------------
# 2. SQLite database file — ephemeral on Vercel. /tmp is the only location
#    guaranteed to survive across Apache worker forks within a single
#    container lifetime; on cold start, /tmp is wiped and the entrypoint
#    re-creates the file and re-runs migrations below. Contact submissions
#    made before a cold restart WILL be lost. Documented in OBS-007.
# ---------------------------------------------------------------------------
DB_FILE="${DB_DATABASE:-$EPHEMERAL_ROOT/database.sqlite}"
DB_DIR="$(dirname "$DB_FILE")"
mkdir -p "$DB_DIR"
[ -f "$DB_FILE" ] || touch "$DB_FILE"

# ---------------------------------------------------------------------------
# 3. Encryption key. The tracked .env ships in the image and holds no
#    secrets. APP_KEY may be provided via Vercel's dashboard env vars;
#    otherwise generate one. Generated keys are NOT persisted across
#    cold starts, which means sessions and signed URLs invalidate on
#    every cold restart — acceptable for a stateless marketing storefront.
# ---------------------------------------------------------------------------
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
if [ -f .env ] \
    && [ -z "${APP_KEY:-}" ] \
    && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# ---------------------------------------------------------------------------
# 3b. ALWAYS materialize DB_DATABASE into .env, pointing at the file the
#     entrypoint just created in step 2. Without this, Laravel falls back
#     to database_path('database.sqlite') = /var/www/html/database/database.sqlite
#     at request time, which on Vercel is in the read-only image layer and
#     crashes the DB connection with "unable to open database file" (surfaces
#     as DatabaseManager.php line 226). OBS-009.
#
#     NOTE: if /var/www/html/.env is read-only (Vercel may mount the image
#     layer read-only even for the entrypoint), this sed -i will FAIL. In
#     that case, config/database.php has a code-level fallback (ADR-009)
#     that detects the read-only path and uses /tmp/storefront/database.sqlite
#     directly — so the app works even without .env materialization.
#
#     This block differs from the Render entrypoint (entrypoint.sh), which
#     only materializes DB_DATABASE when the env var is set. On Vercel we
#     must ALWAYS materialize it because the default Laravel path is in
#     the read-only image filesystem, not in /tmp.
if [ -f .env ]; then
    if grep -q '^DB_DATABASE=' .env; then
        sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_FILE|" .env 2>/dev/null \
            && echo "DB_DATABASE materialized into .env: $DB_FILE" >&2 \
            || echo "WARNING: sed -i .env failed (read-only filesystem?). Relying on config/database.php fallback." >&2
    else
        printf '\nDB_DATABASE=%s\n' "$DB_FILE" >> .env 2>/dev/null \
            && echo "DB_DATABASE appended to .env: $DB_FILE" >&2 \
            || echo "WARNING: could not append to .env (read-only filesystem?). Relying on config/database.php fallback." >&2
    fi
else
    echo "WARNING: .env not found. Relying on config/database.php fallback." >&2
fi

# ---------------------------------------------------------------------------
# 4. Migrations (idempotent — safe on every container start).
#    Non-fatal: if migrate fails (e.g., DB file can't be created), log
#    the error and continue — Apache will still start, and the /__debug
#    route can be used to diagnose. The config-level fallback in
#    config/database.php will point at /tmp/storefront/database.sqlite.
# ---------------------------------------------------------------------------
php artisan migrate --force 2>&1 || {
    echo "WARNING: php artisan migrate failed (exit $?" >&2
    echo "  The app will still start, but contact form submissions may fail." >&2
    echo "  Visit /__debug to diagnose the DB path and file status." >&2
}

# ---------------------------------------------------------------------------
# 5. Apache port. Vercel injects PORT (typically 8080); Apache's Listen
#    directive cannot read env vars at parse time, so the entrypoint
#    substitutes the {{PORT}} placeholder in vhost.vercel.conf with the
#    actual value. Falls back to 8080 if PORT is unset (matches Vercel's
#    default convention; also works for local `docker run -e PORT=...`).
# ---------------------------------------------------------------------------
PORT="${PORT:-8080}"

VHOST_FILE="/etc/apache2/sites-available/storefront.conf"
if [ -f "$VHOST_FILE" ]; then
    sed -i "s|{{PORT}}|$PORT|g" "$VHOST_FILE"
fi

# Also rewrite the default ports.conf so Apache's global Listen directive
# matches the vhost. Without this, Apache fails to start with
# "(22)Invalid argument: make_sock: could not bind to address" when
# ports.conf still says Listen 80 but the vhost binds *:$PORT.
PORTS_FILE="/etc/apache2/ports.conf"
if [ -f "$PORTS_FILE" ]; then
    # Replace the FIRST Listen directive (Apache's default ports.conf has
    # `Listen 80`); preserve any subsequent Listen lines for SSL, etc.
    sed -i "0,/^Listen .*/ s|^Listen .*|Listen $PORT|" "$PORTS_FILE"
fi

# ---------------------------------------------------------------------------
# 6. Writable ownership for the web user. Only meaningful when the
#    container starts as root (the php:apache default); Vercel may invoke
#    the container as a non-root user, in which case chown fails silently
#    (no permission) and the runtime must already be writable by that user.
#    The /tmp relocation in step 1 covers the common case.
# ---------------------------------------------------------------------------
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data \
        storage bootstrap/cache "$EPHEMERAL_ROOT" database 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 7. Hand off to the container command (default: apache2-foreground).
# ---------------------------------------------------------------------------
echo "=== Vercel entrypoint DONE — starting Apache on port $PORT ===" >&2
echo "DB file: $DB_FILE" >&2
echo "Storage: $(ls -la storage 2>&1 | head -1)" >&2
echo ".env DB_DATABASE: $(grep '^DB_DATABASE=' .env 2>/dev/null || echo '<not set>')" >&2
exec "$@"
