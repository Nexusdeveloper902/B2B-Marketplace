# ADR-004

## Date
2026-09-02

## Context
The storefront (TASK-001) is complete on main, and the client asked for a
Dockerfile so the app can be deployed via Docker. The execution environment
has no Docker daemon (OBS-004), so the approach must be verifiable by
simulating every build and runtime step with the local static-PHP toolchain.
Constraints inherited from TASK-001: SQLite only, no Node/Vite, no external
services, tracked secret-free `.env`, self-hosted fonts.

## Decision
Single-container, multi-stage image on `php:8.4-apache`:

- Stage 1 (`composer:2`): `composer install --no-dev --no-scripts
  --no-autoloader` on the lockfile, then full source plus
  `composer dump-autoload --optimize` (fires `artisan package:discover`).
  Keeps git/unzip and the zip extension out of the runtime image.
- Stage 2 (`php:8.4-apache`): `php.ini-production`, OPcache tuned for
  image-immutable code (`validate_timestamps=0`), mod_rewrite, custom vhost
  with DocumentRoot at `public/` and AllowOverride All (routing stays in
  Laravel's `public/.htaccess`). No compiled extensions: every extension
  the lock requires (ctype, dom, fileinfo, filter, hash, iconv, json,
  libxml, mbstring, openssl, pcre, session, tokenizer) ships enabled by
  default in official php images; the lock's PHP floor is >= 8.4.1
  (16 Symfony components), which the 8.4 tag always satisfies.
- Entrypoint (`docker/entrypoint.sh`): recreate writable dirs, create the
  SQLite file at `DB_DATABASE`, APP_KEY bootstrap when `.env` is missing or
  keyless, materialize a provided `DB_DATABASE` into `.env` (web SAPIs do
  not reliably expose container environment variables to PHP — OBS-005),
  `migrate --force`, chown for www-data when started as root, then exec the
  container command.
- `docker-compose.yml`: named volume `storefront-data` with
  `DB_DATABASE=/var/www/storefront-data/database.sqlite`, healthcheck via
  the PHP CLI against Laravel's `/up` route (php:apache images ship no
  curl), `APP_PORT` override, restart unless-stopped.
- `.dockerignore` excludes `.git`, `.agent`, tests, phpunit artifacts, host
  vendor, runtime state, and the Docker build files themselves.

## Alternatives Considered
- php:fpm + nginx (sidecar or supervisor): more moving parts, no benefit at
  this traffic level — rejected.
- `php artisan serve` / `php -S` as the runtime: development server — rejected.
- Installing deps at container start instead of build: slower, less
  reproducible deploys — rejected.
- `docker-php-ext-install` for runtime extensions: unnecessary — the default
  image set covers all requirements (verified against the lock, not assumed).
- SQLite in the container filesystem only (no volume): contact submissions
  (the only business data) would be lost on redeploy — rejected.
- config/route/view caching at startup: omitted deliberately — a five-route
  static site gains nothing; less to go wrong; can be added later if needed.

## Reasoning
The app is a small, single-process, SQLite-backed Blade site, so the simplest
production-faithful unit is one Apache+mod_php container. The
zero-extension claim was verified empirically rather than assumed. The
database location is materialized into `.env` by the entrypoint because
environment-variable visibility differs per SAPI (OBS-005) — `.env` is the
one configuration channel every runtime reads.

## Consequences
- Deployment is `docker build` / `docker compose up -d --build`; nothing else.
- The `composer:2` stage image must run PHP >= 8.4.1. If a future composer
  image regressed below that, the stage-1 install fails loudly ("require
  >= 8.4.1"); the fix is a composer image with a newer PHP.
- Overriding `ARG PHP_VERSION` below 8.4 fails the lock's platform check at
  runtime — loud by design.
- Future agents must keep `.env` secret-free, must not configure
  MySQL/Postgres, must keep the single-container shape (extend the
  entrypoint rather than adding sidecars), and must keep `.agent/` and tests
  out of the image via `.dockerignore`.
- The image build itself was NOT executed in this environment (no daemon);
  it was verified by exact step replication and static review — see
  RUN-2026-09-02-marketplace-003.

## Status
ACTIVE

## Supersedes
none
