# STATE SNAPSHOT — RUN-2026-09-02-marketplace-011

## Overall Status
PARTIAL — 4 Vercel runtime crash fixes applied and pushed. The cascade
of bootstrap crashes has been systematically resolved. Final Vercel
verification pending user redeploy of commit e896c84.

## Completed
- TASK-001-marketplace-mvp — five-page bilingual storefront (RUN-002)
- TASK-002-docker-deployment — Render Docker image (RUN-003, ADR-004)
- TASK-003-render-styling-fix — forceScheme for TLS proxies (RUN-004, ADR-005)
- TASK-004-vercel-deployment — initial Vercel variant (RUN-005, ADR-006)
- TASK-005-vercel-docker-builder-fix — services config (RUN-006, ADR-007→superseded by ADR-010)
- TASK-006-vercel-db-path-fix — .env materialization (RUN-007, ADR-008)
- TASK-007-vercel-config-level-fallbacks — config defaults (RUN-008, ADR-009)
- TASK-008-vercel-services-config — Dockerfile.vercel + services (RUN-009, ADR-010)
- TASK-009-switch-to-frankenphp — FrankenPHP runtime (RUN-010, ADR-011)
- TASK-010-vercel-storage-env-overrides — storage path + env loading fixes (this run, RUN-011, ADR-012)

## In Progress
- Vercel live verification of commit e896c84 — user must redeploy.
  The last known error was `foreach() argument must be of type
  array|object, string given` at landing.blade.php:41, caused by
  APP_LOCALE not being set. The fix loads ALL env vars from .env via
  `set -a; . ./.env; set +a`.

## Blocked
- None at the code layer.

## Known Problems
- Vercel's ephemeral filesystem (OBS-007) — contact submissions lost on
  cold restart. Acceptable for marketing demo.
- `/__debug` route is unauthenticated — remove before production.
- `APP_DEBUG=true` is set temporarily in the entrypoint — change to
  `false` once deployment is confirmed working.
- `set -x` in the entrypoint produces verbose Vercel logs — remove
  once deployment is confirmed working.
- Old Apache-based Vercel files (docker/apache/vhost.vercel.conf,
  docker/entrypoint.vercel.sh) are unused — can be cleaned up.
- phpdotenv doesn't load .env under FrankenPHP (OBS-015) — the
  `set -a; . ./.env; set +a` workaround in the entrypoint is required.

## Important Current Facts
- Vercel Dockerfile uses FrankenPHP (dunglas/frankenphp:1-php8.4).
- Caddyfile listens on :{$PORT:80} — no entrypoint port patching needed.
- entrypoint.frankenphp.sh:
  - Step 1b: `set -a; . ./.env; set +a` — loads ALL env vars from .env
  - Step 2b: unconditional overrides for SESSION_DRIVER=cookie,
    CACHE_STORE=array, APP_MAINTENANCE_DRIVER=cache,
    APP_MAINTENANCE_STORE=array, LOG_CHANNEL=stderr,
    VIEW_COMPILED_PATH=/tmp/..., APP_DEBUG=true
  - DB_DATABASE=/tmp/storefront/database.sqlite
- config/view.php supports VIEW_COMPILED_PATH env var.
- config/database.php has a code-level SQLite path fallback.
- config/session.php defaults to 'file' (was 'database').
- config/cache.php defaults to 'file' (was 'database').
- Render deployment still uses Apache+mod_php (Dockerfile + entrypoint.sh + vhost.conf).
- vercel.json has services + runtime:container + rewrites (ADR-010).
- The `set -a; . ./.env; set +a` approach is required because
  phpdotenv doesn't load .env under FrankenPHP (OBS-015).
- The unconditional env var overrides (not ${VAR:-default}) are
  required because .env sets them to 'file' which would break on
  Vercel's read-only FS.

## Current Main Commit
e896c84 — fix(vercel): load ALL .env vars via set -a (fix translation loader + APP_KEY)

## Current Main Status
BUILDABLE — 4 commits to docker/entrypoint.frankenphp.sh + 1 new
config/view.php. No changes to application code, migrations, models,
controllers, Blade templates (beyond the existing ones), or the Render
deployment.

## Active Branches
- main (default; all task branches merged historically)
