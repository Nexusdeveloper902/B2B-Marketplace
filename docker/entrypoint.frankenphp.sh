#!/bin/sh
# Presence Platform — Marketplace Storefront: FrankenPHP container entrypoint.
#
# This is the SIMPLIFIED entrypoint for FrankenPHP (replaces the Apache-based
# entrypoint.vercel.sh). FrankenPHP reads env vars natively and binds to $PORT
# via the Caddyfile — no Apache port patching, no vhost templating, no
# SetEnv/PassEnv directives needed.
#
# What this entrypoint does:
#   1. Creates writable directories in /tmp (Vercel's ephemeral FS)
#   2. Symlinks storage/ to /tmp/storage
#   3. Creates the SQLite database file at /tmp
#   4. Generates APP_KEY if missing
#   5. Runs migrations
#   6. Execs frankenphp (which reads $PORT from the env, not from a config file)
#
# See .agent/DECISIONS/ADR-011-switch-to-frankenphp.md for why we switched
# from Apache+mod_php to FrankenPHP.

set -eu

APP_DIR="${APP_DIR:-/app}"
cd "$APP_DIR"

# Diagnostics: trace to stderr so Vercel logs show what happened.
set -x

echo "=== FrankenPHP entrypoint START ===" >&2
echo "APP_DIR=$APP_DIR" >&2
echo "PORT=${PORT:-<unset, Caddyfile defaults to 80>}" >&2
echo "id=$(id)" >&2

# ---------------------------------------------------------------------------
# 1. Writable runtime directories — relocated to /tmp (Vercel ephemeral FS).
#    FrankenPHP runs as www-data; /app may be read-only, so we symlink
#    storage/ and bootstrap/cache to /tmp.
# ---------------------------------------------------------------------------
EPHEMERAL_ROOT="${EPHEMERAL_ROOT:-/tmp/storefront}"
STORAGE_DIR="$EPHEMERAL_ROOT/storage"

mkdir -p \
    "$STORAGE_DIR/framework/views" \
    "$STORAGE_DIR/framework/sessions" \
    "$STORAGE_DIR/framework/cache/data" \
    "$STORAGE_DIR/logs" \
    "$EPHEMERAL_ROOT/bootstrap-cache"

# Symlink storage/ if it's not already a symlink. Non-fatal: if /app is
# read-only, try subdirectory symlinks as fallback.
if [ ! -L storage ]; then
    if rm -rf storage 2>/dev/null && ln -s "$STORAGE_DIR" storage 2>/dev/null; then
        echo "storage/ symlinked to $STORAGE_DIR" >&2
    else
        echo "WARNING: could not symlink storage/ — trying subdirs..." >&2
        mkdir -p storage/framework storage/logs 2>/dev/null || true
        for sub in framework/views framework/sessions framework/cache/data logs; do
            rm -rf "storage/$sub" 2>/dev/null || true
            ln -s "$STORAGE_DIR/$sub" "storage/$sub" 2>/dev/null || true
        done
    fi
fi

# Symlink bootstrap/cache if needed.
if [ ! -L bootstrap/cache ] && [ ! -w bootstrap/cache ]; then
    rm -rf bootstrap/cache 2>/dev/null && ln -s "$EPHEMERAL_ROOT/bootstrap-cache" bootstrap/cache 2>/dev/null \
        && echo "bootstrap/cache symlinked" >&2 \
        || echo "WARNING: could not symlink bootstrap/cache" >&2
fi

# ---------------------------------------------------------------------------
# 2. SQLite database file at /tmp (ephemeral, but writable by www-data).
#    DB_DATABASE env var is read natively by FrankenPHP → PHP → Laravel's env().
#    No .env materialization needed! (Unlike Apache+mod_php, FrankenPHP passes
#    env vars to PHP automatically.)
# ---------------------------------------------------------------------------
DB_FILE="${DB_DATABASE:-$EPHEMERAL_ROOT/database.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
[ -f "$DB_FILE" ] || touch "$DB_FILE"
echo "DB file: $DB_FILE" >&2

# Export DB_DATABASE so Laravel's env() picks it up.
# FrankenPHP passes OS env vars to PHP natively (unlike Apache+mod_php).
export DB_DATABASE="$DB_FILE"

# ---------------------------------------------------------------------------
# 2b. Override ALL storage paths via env vars (FrankenPHP reads them natively).
#
#     On Vercel, /app is in the read-only image layer. The storage/ symlink
#     approach (step 1) fails silently because rm -rf storage can't execute
#     on a read-only filesystem. Without these env var overrides, Laravel
#     crashes during bootstrap because it can't:
#       - Compile Blade views (storage/framework/views/)
#       - Write session files (storage/framework/sessions/)
#       - Write cache files (storage/framework/cache/data/)
#       - Write logs (storage/logs/)
#
#     These env vars are read by FrankenPHP → PHP → Laravel's env(), which
#     overrides the config defaults. This is the ONLY reliable way to redirect
#     Laravel's file I/O on a read-only filesystem.
# ---------------------------------------------------------------------------
mkdir -p \
    "$EPHEMERAL_ROOT/framework/views" \
    "$EPHEMERAL_ROOT/framework/sessions" \
    "$EPHEMERAL_ROOT/framework/cache/data" \
    "$EPHEMERAL_ROOT/logs"

# Blade compiled views → /tmp (read by config/view.php via VIEW_COMPILED_PATH)
export VIEW_COMPILED_PATH="$EPHEMERAL_ROOT/framework/views"

# Sessions → cookie driver (no file I/O, no DB I/O — safest for ephemeral FS)
export SESSION_DRIVER="${SESSION_DRIVER:-cookie}"

# Cache → array driver (no file I/O, no DB I/O — in-memory per request)
export CACHE_STORE="${CACHE_STORE:-array}"

# Logs → stderr (captured by Vercel's log system, no file I/O)
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export LOG_STACK="${LOG_STACK:-stderr}"

# APP_DEBUG → true temporarily to expose any remaining errors. Set to false
# once the deployment is confirmed working.
export APP_DEBUG="${APP_DEBUG:-true}"
export APP_ENV="${APP_ENV:-production}"

echo "VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH" >&2
echo "SESSION_DRIVER=$SESSION_DRIVER" >&2
echo "CACHE_STORE=$CACHE_STORE" >&2
echo "LOG_CHANNEL=$LOG_CHANNEL" >&2

# ---------------------------------------------------------------------------
# 3. APP_KEY — generate if not set. FrankenPHP passes it to PHP natively.
# ---------------------------------------------------------------------------
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# Generate APP_KEY if .env doesn't have one. Since FrankenPHP reads env vars
# natively, we can also export APP_KEY if it's set as a container env var.
if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    if [ -z "${APP_KEY:-}" ]; then
        php artisan key:generate --force 2>/dev/null || echo "WARNING: key:generate failed" >&2
    fi
fi

# ---------------------------------------------------------------------------
# 4. Migrations (idempotent). Non-fatal: if migrate fails, the app still
#    starts — /__debug can be used to diagnose.
# ---------------------------------------------------------------------------
php artisan migrate --force 2>&1 || {
    echo "WARNING: migrate failed (exit $?)" >&2
    echo "  The app will still start. Visit /__debug to diagnose." >&2
}

# ---------------------------------------------------------------------------
# 5. Fix permissions (if running as root).
# ---------------------------------------------------------------------------
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data "$EPHEMERAL_ROOT" storage bootstrap/cache 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 6. Hand off to FrankenPHP. It reads $PORT from the environment and binds
#    to :{$PORT:80} per the Caddyfile. No port patching needed!
# ---------------------------------------------------------------------------
echo "=== FrankenPHP entrypoint DONE — starting FrankenPHP ===" >&2
echo "DB_DATABASE=$DB_DATABASE" >&2
echo "APP_KEY set: $([ -n \"${APP_KEY:-}\" ] && echo yes || echo 'checking .env')" >&2

exec "$@"
