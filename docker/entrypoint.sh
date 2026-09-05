#!/bin/sh
# Presence Platform — Marketplace Storefront: container entrypoint.
#
# Prepares everything the runtime needs that cannot live in an image layer:
#   1. writable framework directories (recreated if a volume shadows them)
#   2. an encryption key, only if the shipped .env is missing or keyless
#   3. ownership for the web user (when started as root)
# then hands control to the container command (default: apache2-foreground).
#
# The storefront is STATELESS (see .agent/DECISIONS/ADR-013-stateless-no-database.md):
# there is no database and no migrations. Contact requests are written to the
# application log, not persisted. The SQLite materialization and migrate steps
# from earlier runs were removed in TASK-011.

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

# 2. Encryption key. The image no longer ships a tracked .env (it was removed
#    from the repository for secret hygiene); .env is materialized from
#    .env.example and keyed at startup. When APP_KEY is set as a real
#    environment variable, generation is skipped entirely.
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
if [ -f .env ] \
    && [ -z "${APP_KEY:-}" ] \
    && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# 3. Writable ownership for the web user. Only meaningful when the
#    container starts as root (the php:apache default); hardened non-root
#    setups must mount writable volumes instead.
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi

exec "$@"
