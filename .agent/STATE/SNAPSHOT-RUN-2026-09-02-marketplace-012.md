# STATE SNAPSHOT — RUN-2026-09-02-marketplace-012

## Overall Status
HEALTHY — Vercel deployment confirmed working by user. Production cleanup
applied (APP_DEBUG=false, set -x removed). Both Vercel and Render are
stable deployment targets.

## Completed
- TASK-001-marketplace-mvp — five-page bilingual storefront (RUN-002)
- TASK-002-docker-deployment — Render Docker image (RUN-003, ADR-004)
- TASK-003-render-styling-fix — forceScheme for TLS proxies (RUN-004, ADR-005)
- TASK-004-vercel-deployment — initial Vercel variant (RUN-005, ADR-006)
- TASK-005-vercel-docker-builder-fix — services config (RUN-006)
- TASK-006-vercel-db-path-fix — .env materialization (RUN-007, ADR-008)
- TASK-007-vercel-config-level-fallbacks — config defaults (RUN-008, ADR-009)
- TASK-008-vercel-services-config — Dockerfile.vercel + services (RUN-009, ADR-010)
- TASK-009-switch-to-frankenphp — FrankenPHP runtime (RUN-010, ADR-011)
- TASK-010-vercel-storage-env-overrides — storage + env fixes (RUN-011, ADR-012)
- TASK-010 verification + cleanup (RUN-012, this run, OBS-016)

## In Progress
- None. Both deployment targets are working.

## Blocked
- None.

## Known Problems
- Vercel's ephemeral filesystem (OBS-007) — contact submissions lost on
  cold restart. Acceptable for marketing demo.
- `/__debug` route is unauthenticated — remove before production.
- Old Apache-based Vercel files (docker/apache/vhost.vercel.conf,
  docker/entrypoint.vercel.sh) are unused — can be cleaned up (low priority).
- phpdotenv doesn't load .env under FrankenPHP (OBS-015) — the
  `set -a; . ./.env; set +a` workaround in the entrypoint is required.

## Important Current Facts
- **Vercel deployment: WORKING** (confirmed by user, OBS-016)
- Vercel Dockerfile uses FrankenPHP (dunglas/frankenphp:1-php8.4).
- entrypoint.frankenphp.sh:
  - Step 1b: `set -a; . ./.env; set +a` — loads ALL env vars
  - Step 2b: unconditional overrides for SESSION_DRIVER=cookie,
    CACHE_STORE=array, APP_MAINTENANCE_DRIVER=cache, LOG_CHANNEL=stderr,
    VIEW_COMPILED_PATH=/tmp/..., APP_DEBUG=false
  - DB_DATABASE=/tmp/storefront/database.sqlite
- config/view.php supports VIEW_COMPILED_PATH env var.
- Render deployment still uses Apache+mod_php (Dockerfile + entrypoint.sh).
- vercel.json has services + runtime:container + rewrites (ADR-010).
- APP_DEBUG is now false (production mode).
- set -x has been removed from the entrypoint.

## Current Main Commit
(recorded after commit + push)

## Current Main Status
BUILDABLE — production cleanup only (APP_DEBUG=false, set -x removed).
No functional changes. Vercel deployment confirmed working by user.

## Active Branches
- main (default)
