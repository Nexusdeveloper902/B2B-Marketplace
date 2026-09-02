#!/bin/sh
# Presence Platform — Marketplace Storefront: container entrypoint.
#
# Prepares everything the runtime needs that cannot live in an image layer:
#   1. writable framework directories (recreated if a volume shadows them)
#   2. the SQLite database file (location from DB_DATABASE when set)
#   3. an encryption key, only if the shipped .env is missing or keyless
#   4. migrations (idempotent — safe on every start)
#   5. ownership for the web user (when started as root)
# then hands control to the container command (default: apache2-foreground).

set -eu

APP_DIR="${APP_DIR:-/var/www/html}"
cd "$APP_DIR"

# 1. Runtime directories.
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# 2. SQLite database file. docker-compose.yml points DB_DATABASE at a named
#    volume so contact submissions survive rebuilds; a plain `docker run`
#    keeps the in-image default location.
DB_FILE="${DB_DATABASE:-$APP_DIR/database/database.sqlite}"
DB_DIR="$(dirname "$DB_FILE")"
mkdir -p "$DB_DIR"
[ -f "$DB_FILE" ] || touch "$DB_FILE"

# 3. Encryption key. The tracked .env ships in the image and holds no
#    secrets; keep zero-config startup, but cover the case where .env was
#    excluded from the build or stripped of its key. When APP_KEY is set
#    as a real environment variable, generation is skipped entirely.
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
if [ -f .env ] \
    && [ -z "${APP_KEY:-}" ] \
    && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# 3b. Database location. When DB_DATABASE is provided (docker-compose.yml
#     does, to place SQLite on a volume), apply it to .env: web SAPIs do
#     not reliably expose container environment variables to PHP, but
#     every runtime reads .env. The CLI (migrate below) sees the real
#     environment variable directly; both resolve to the same file.
if [ -n "${DB_DATABASE:-}" ] && [ -f .env ]; then
    if grep -q '^DB_DATABASE=' .env; then
        sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
    else
        printf '\nDB_DATABASE=%s\n' "$DB_DATABASE" >> .env
    fi
fi

# 4. Migrations (Laravel would auto-create the SQLite file as well; the
#    touch above just makes the intent explicit).
php artisan migrate --force

# 5. Writable ownership for the web user. Only meaningful when the
#    container starts as root (the php:apache default); hardened non-root
#    setups must mount writable volumes instead.
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache "$DB_DIR" database
fi

exec "$@"
