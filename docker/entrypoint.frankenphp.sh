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
#   3. Loads env vars from .env (falling back to .env.example — the repo no
#      longer tracks .env; sourcing performs no writes, which matters because
#      Vercel's filesystem is read-only, see OBS-010)
#   4. Generates APP_KEY at startup if none was loaded/provided
#   5. Execs frankenphp (which reads $PORT from the env, not from a config file)
#
# The storefront is STATELESS (see .agent/DECISIONS/ADR-013-stateless-no-database.md):
# no database, no migrations. Contact requests go to the application log
# (stderr here), not to storage. The SQLite materialization and migrate steps
# from earlier runs were removed in TASK-011.
#
# See .agent/DECISIONS/ADR-011-switch-to-frankenphp.md for why we switched
# from Apache+mod_php to FrankenPHP.

set -eu

APP_DIR="${APP_DIR:-/app}"
cd "$APP_DIR"

# Lightweight startup banner (kept for Vercel log visibility).
# Note: `set -x` was used during debugging (RUN-011) but removed once
# the deployment was confirmed working — it produced excessive log noise.
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

# ---------------------------------------------------------------------------
# 1b. Load ALL env vars into the shell environment (BEFORE overrides).
#     FrankenPHP reads env vars natively and passes them to PHP. phpdotenv
#     (Laravel's .env loader) isn't loading /app/.env properly under
#     FrankenPHP's persistent process model, so we export every KEY=VALUE
#     ourselves and let FrankenPHP pass them all to PHP.
#
#     Since TASK-011 the repository no longer tracks .env (secret hygiene).
#     When .env is absent from the image we source .env.example DIRECTLY:
#     sourcing reads without writing, which is safe on Vercel's read-only
#     filesystem (a `cp .env.example .env` here would fail under set -e,
#     see OBS-010). Values from a real .env still win when present.
#
#     This must happen BEFORE the Vercel-specific overrides (step 2) so that
#     the overrides (SESSION_DRIVER=cookie, CACHE_STORE=array, etc.) take
#     priority over the sourced values (SESSION_DRIVER=file, etc.).
# ---------------------------------------------------------------------------
ENV_FILE=""
if [ -f .env ]; then
    ENV_FILE=.env
elif [ -f .env.example ]; then
    ENV_FILE=.env.example
fi
if [ -n "$ENV_FILE" ]; then
    set -a
    # shellcheck disable=SC1090
    . "./$ENV_FILE"
    set +a
    echo "Loaded env vars from $ENV_FILE (APP_LOCALE, SESSION_*, etc.)" >&2
fi

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
# 2. Override ALL storage paths via env vars (FrankenPHP reads them natively).
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
export SESSION_DRIVER=cookie

# Cache → array driver (no file I/O, no DB I/O — in-memory per request)
export CACHE_STORE=array

# Maintenance mode → cache driver with array store (no file I/O, no DB I/O).
# The default 'file' driver writes to storage/framework/ which is read-only
# on Vercel, causing MaintenanceModeManager::getDefaultDriver() to crash
# during bootstrap (ArgumentCountError at Manager::createDriver).
# The 'cache' driver with 'array' store is per-request, non-persistent,
# but we never enable maintenance mode on this demo anyway.
export APP_MAINTENANCE_DRIVER=cache
export APP_MAINTENANCE_STORE=array

# Logs → stderr (captured by Vercel's log system, no file I/O). Contact
# requests are logged here — this is now the ONLY record of a submission.
export LOG_CHANNEL=stderr
export LOG_STACK=stderr

# APP_DEBUG → false (production). Was set to true during RUN-011 debugging
# to expose bootstrap errors; reverted now that the deployment is confirmed working.
export APP_DEBUG=false
export APP_ENV=production

echo "VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH" >&2
echo "SESSION_DRIVER=$SESSION_DRIVER" >&2
echo "CACHE_STORE=$CACHE_STORE" >&2
echo "LOG_CHANNEL=$LOG_CHANNEL" >&2

# ---------------------------------------------------------------------------
# 3. Encryption key (replaces the old migrate step — TASK-011 / ADR-013).
#    The image carries no .env, and .env.example ships a keyless APP_KEY=,
#    so generate one at container start unless APP_KEY was provided as a
#    real deployment environment variable (Vercel project settings).
#    `key:generate --show` prints a fresh key without touching the
#    filesystem — required on Vercel's read-only FS.
#
#    Consequence: each cold start gets a fresh key, so cookie sessions are
#    invalidated across deploys/restarts. Acceptable for a stateless
#    marketing site with no authenticated state.
# ---------------------------------------------------------------------------
if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="$(php artisan key:generate --show --no-interaction)"
    export APP_KEY
    echo "APP_KEY generated at container start (no key in env or .env)" >&2
fi

# ---------------------------------------------------------------------------
# 4. Fix permissions (if running as root).
# ---------------------------------------------------------------------------
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data "$EPHEMERAL_ROOT" storage bootstrap/cache 2>/dev/null || true
fi

# ---------------------------------------------------------------------------
# 5. Hand off to FrankenPHP. It reads $PORT from the environment and binds
#    to :{$PORT:80} per the Caddyfile. No port patching needed!
# ---------------------------------------------------------------------------
echo "=== FrankenPHP entrypoint DONE — starting FrankenPHP ===" >&2
echo "APP_KEY set: $([ -n \"${APP_KEY:-}\" ] && echo yes || echo no)" >&2

exec "$@"
