# syntax=docker/dockerfile:1

# Presence Platform — Marketplace Storefront
# Render / docker-compose deployment target.
#
# This is the repo's default `Dockerfile` — used by `docker-compose.yml`
# and by Render (which defaults to `Dockerfile`). The Vercel deployment
# target is `Dockerfile.vercel` (see ADR-010 / OBS-011 for why Vercel
# requires the `.vercel` suffix and a `vercel.json` with `services` +
# `runtime: "container"`).
#
# Production image: Apache + mod_php + SQLite. No Node, no external services.
#   Build:   docker build -t presence-platform-storefront .
#   Run:     docker run --rm -p 8080:80 presence-platform-storefront
#   Compose: docker compose up -d --build   (docker-compose.yml references this file)
#
# Approach recorded in .agent/DECISIONS/ADR-004-docker-deployment.md.

ARG PHP_VERSION=8.4

# ---------------------------------------------------------------------------
# Stage 1 — dependencies.
# Composer packages without dev tools, plus the optimized autoloader. Using
# the official composer image keeps git/unzip (and the zip extension) out of
# the runtime image entirely.
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Cached dependency layer: re-installs only when the lockfile changes.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

# Full source (build context minus .dockerignore), then the classmap.
# `dump-autoload` fires Laravel's post-autoload-dump hook
# (artisan package:discover), which needs the whole app present.
COPY . .
RUN composer dump-autoload --optimize --no-interaction

# ---------------------------------------------------------------------------
# Stage 2 — runtime.
# php:8.4-apache already ships every extension this app requires:
# pdo_sqlite, sqlite3, mbstring, openssl, dom, tokenizer, ctype, curl,
# fileinfo. Nothing is compiled beyond OPcache.
# Note: the lockfile's Symfony components set a PHP >= 8.4.1 floor — the
# 8.4 tag tracks the latest patch release, so it always satisfies it.
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-apache

# Production PHP settings, OPcache for image-immutable code, clean vhost.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && docker-php-ext-install opcache \
    && a2enmod rewrite \
    && echo 'ServerName storefront' \
        > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY docker/php/opcache.ini "$PHP_INI_DIR/conf.d/zz-opcache.ini"
COPY docker/apache/vhost.conf /etc/apache2/sites-available/storefront.conf
RUN a2dissite 000-default \
    && a2ensite storefront

# Application + vendor + the tracked, secret-free .env from stage 1.
COPY --from=vendor --chown=www-data:www-data /app /var/www/html

COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/storefront-entrypoint

EXPOSE 80

# Prepares writable directories, the SQLite file and migrations, then execs
# the container command (default: apache2-foreground).
ENTRYPOINT ["storefront-entrypoint"]
CMD ["apache2-foreground"]
