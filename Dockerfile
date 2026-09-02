# syntax=docker/dockerfile:1

# Presence Platform — Marketplace Storefront
# Primary Dockerfile (Vercel Container Deployment target).
#
# This is the repo's default `Dockerfile`, so Vercel auto-detects it on
# import — no vercel.json / @vercel/docker builder needed (that builder
# was deprecated and removed from Vercel's npm registry on 2025-08-18;
# see .agent/OBSERVATIONS/OBS-008-vercel-docker-builder-deprecated.md).
#
# Why this Dockerfile is Vercel-shaped (not Render-shaped):
#   - Vercel injects a $PORT env var the container must bind to (Apache's
#     Listen directive can't read env vars at parse time — the entrypoint
#     rewrites ports.conf + the vhost at startup).
#   - Vercel's container filesystem is EPHEMERAL: SQLite data and Laravel
#     storage/ state do not survive cold starts. The entrypoint relocates
#     them to /tmp/ (the only guaranteed-writable location) so the app
#     runs at all, but contact submissions will be lost on cold restart.
#     See .agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md.
#   - Vercel may run the container as non-root; the entrypoint skips the
#     chown step when not root.
#
# Build locally for testing:
#   docker build -t storefront-vercel .
#   docker run --rm -p 8080:8080 -e PORT=8080 storefront-vercel
#
# The Render deployment target uses Dockerfile.render (originally the
# repo's default Dockerfile; renamed in RUN-2026-09-02-marketplace-006
# so Vercel could auto-detect this file). docker-compose.yml points at
# Dockerfile.render for the persistent-volume local-deploy flow.
#
# Approach recorded in .agent/DECISIONS/ADR-007-vercel-default-dockerfile.md
# (supersedes ADR-006, which used the now-removed @vercel/docker builder).
# Mirrors ADR-004's Apache + mod_php + SQLite shape; the differences are
# isolated to the vhost (port templated at runtime) and the entrypoint
# (ephemeral-FS relocation + $PORT patching).

ARG PHP_VERSION=8.4

# ---------------------------------------------------------------------------
# Stage 1 — dependencies (identical to the Render Dockerfile's stage 1;
# cached independently because Vercel builds from this Dockerfile context).
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

COPY . .
RUN composer dump-autoload --optimize --no-interaction

# ---------------------------------------------------------------------------
# Stage 2 — runtime.
# php:8.4-apache ships every extension this app requires (pdo_sqlite,
# sqlite3, mbstring, openssl, dom, tokenizer, ctype, curl, fileinfo).
# The lockfile's Symfony components set a PHP >= 8.4.1 floor — the 8.4 tag
# always satisfies it. Nothing is compiled beyond OPcache.
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-apache

# Production PHP settings, OPcache for image-immutable code, mod_rewrite,
# ServerName hint so Apache stops warning on startup.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && docker-php-ext-install opcache \
    && a2enmod rewrite \
    && echo 'ServerName storefront' \
        > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY docker/php/opcache.ini "$PHP_INI_DIR/conf.d/zz-opcache.ini"

# Vercel-specific vhost: port is templated at runtime by the entrypoint
# because Apache's Listen directive cannot read $PORT at parse time.
COPY docker/apache/vhost.vercel.conf /etc/apache2/sites-available/storefront.conf
RUN a2dissite 000-default \
    && a2ensite storefront

# Application + vendor + the tracked, secret-free .env from stage 1.
COPY --from=vendor --chown=www-data:www-data /app /var/www/html

COPY --chmod=0755 docker/entrypoint.vercel.sh /usr/local/bin/storefront-entrypoint

# EXPOSE is documentation only — Vercel routes traffic to whatever the
# container listens on per $PORT. Default to 8080 to match Vercel's
# convention; the entrypoint patches Apache to the actual $PORT at runtime.
EXPOSE 8080

# Prepares ephemeral writable dirs, patches Apache's listen port from
# $PORT, runs migrations, then execs the container command
# (default: apache2-foreground).
ENTRYPOINT ["storefront-entrypoint"]
CMD ["apache2-foreground"]
