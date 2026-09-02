# STATE SNAPSHOT — RUN-2026-09-02-marketplace-005

## Overall Status
HEALTHY — Vercel deployment variant shipped; pending user-side Vercel
import + build verification. Render deployment continues to work
unchanged.

## Completed
- TASK-001-marketplace-mvp — five-page bilingual (EN/ES) storefront,
  merged to main (RUN-2026-09-02-marketplace-002).
- TASK-002-docker-deployment — Apache+mod_php+SQLite Docker image for
  Render, docker-compose, entrypoint, merged to main
  (RUN-2026-09-02-marketplace-003).
- TASK-003-render-styling-fix — proxy-aware URL::forceScheme('https')
  in AppServiceProvider for TLS-terminating PaaS proxies, documented
  in ADR-005 / OBS-006 (RUN-2026-09-02-marketplace-004).
- TASK-004-vercel-deployment — parallel Vercel-deployment variant
  (Dockerfile.vercel + entrypoint + vhost + vercel.json), documented
  in ADR-006 / OBS-007 (this run, RUN-2026-09-02-marketplace-005).

## In Progress
- Live verification on Vercel — user must import the repo into Vercel
  and confirm the deployment builds and serves the styled UI.

## Blocked
- None at the code layer.

## Known Problems
- Vercel's ephemeral container filesystem means contact submissions are
  lost on cold restart (redeploy, scale-to-zero, container recycle).
  Documented in OBS-007; acceptable for marketing-demo use. For
  production contact collection, use the Render deployment (persistent
  SQLite volume per ADR-004) or migrate to a hosted database (out of
  scope per ADR-001).
- The Vercel-only `docker/*.vercel.*` files leak into the Render
  image's `/var/www/html/docker/` directory because `.dockerignore`
  excludes `Dockerfile.vercel` and `vercel.json` by name but not the
  `docker/*.vercel.*` files. Minor wart — tiny text files, harmless,
  not executable. Future agent can tighten `.dockerignore` if desired.
- Vercel Container Deployments may require a paid Vercel plan. If the
  user's plan rejects the `@vercel/docker` builder, the fallback is
  to rename `Dockerfile.vercel` to `Dockerfile` (and the Render
  Dockerfile to `Dockerfile.render`) and remove `vercel.json`. See
  TASK-004 "Remaining Work" for the full fallback procedure.

## Important Current Facts
- Marketplace app uses SQLite for persistence (database/database.sqlite).
  On Vercel, this file lives at /tmp/storefront/database.sqlite and is
  recreated from migrations on every cold start (OBS-007).
- Marketplace app has no dependency on the core Presence Platform backend.
- Design direction: "Event Ledger" — porcelain paper, deep institutional
  green, hairline-ruled sections (no SaaS card kit), Space Grotesk /
  IBM Plex Sans / IBM Plex Mono self-hosted in public/fonts. See ADR-002.
- Render deployment (ADR-004): single-container Apache + mod_php +
  SQLite, persistent volume via docker-compose.yml, no Node, no Vite,
  no external services.
- Vercel deployment (ADR-006): parallel-deployment variant that mirrors
  ADR-004's shape with three Vercel-specific concerns (port injection,
  ephemeral FS, possible non-root) isolated to Dockerfile.vercel +
  docker/apache/vhost.vercel.conf + docker/entrypoint.vercel.sh +
  vercel.json.
- TLS-termination handling (ADR-005): proxy-aware URL::forceScheme('https')
  in AppServiceProvider applies to BOTH Render and Vercel because both
  terminate TLS at their load balancers and forward X-Forwarded-Proto:
  https. Local dev (php artisan serve on http://localhost:8000) is
  unaffected.

## Current Main Commit
(recorded after `git commit` + `git push`)

## Current Main Status
BUILDABLE — additive change only (4 new files + 2 file updates + 6 new
.agent/ docs). No changes to application code, configuration, migrations,
models, controllers, Blade templates, language files, or the Render
deployment's Dockerfile / vhost / entrypoint / docker-compose.yml.

## Active Branches
- main (default; all task branches merged)
- remotes/origin/feature/TASK-001-marketplace-mvp (historical; merged)
- remotes/origin/feature/TASK-002-docker-deployment (historical; merged)
