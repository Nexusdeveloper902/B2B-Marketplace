# STATE SNAPSHOT — RUN-2026-09-02-marketplace-010

## Overall Status
HEALTHY — switched Vercel deployment to FrankenPHP (Vercel's officially
recommended PHP runtime). Pending user-side Vercel redeploy.

## Completed
- TASK-001 through TASK-008 (prior runs).
- TASK-009-switch-to-frankenphp (this run, ADR-011, OBS-012).

## In Progress
- Live verification on Vercel — user must redeploy.

## Known Problems
- Vercel ephemeral filesystem (OBS-007) — contact submissions lost on cold restart.
- /__debug route is unauthenticated — remove before production.
- Old Apache-based Vercel files (docker/apache/vhost.vercel.conf,
  docker/entrypoint.vercel.sh) are unused — can be cleaned up later.

## Important Current Facts
- Vercel Dockerfile uses FrankenPHP (dunglas/frankenphp:1-php8.4).
- Caddyfile listens on :{$PORT:80} — no entrypoint port patching needed.
- entrypoint.frankenphp.sh exports DB_DATABASE — FrankenPHP reads it natively.
- Render deployment still uses Apache+mod_php (Dockerfile + entrypoint.sh + vhost.conf).
- vercel.json unchanged (services + runtime:container + rewrites).

## Current Main Commit
(recorded after commit + push)

## Current Main Status
BUILDABLE — new Dockerfile.vercel + Caddyfile + entrypoint; Render unchanged.

## Active Branches
- main (default)
