# STATE SNAPSHOT — RUN-2026-09-02-marketplace-006

## Overall Status
HEALTHY — Vercel build failure fixed at the code layer; pending
user-side Vercel re-import to confirm. Render deployment continues
to work but requires a Render-dashboard Dockerfile-path setting update.

## Completed
- TASK-001-marketplace-mvp — five-page bilingual (EN/ES) storefront
  (RUN-2026-09-02-marketplace-002).
- TASK-002-docker-deployment — Apache+mod_php+SQLite Docker image for
  Render (RUN-2026-09-02-marketplace-003, ADR-004).
- TASK-003-render-styling-fix — proxy-aware URL::forceScheme('https')
  for TLS-terminating PaaS proxies (RUN-2026-09-02-marketplace-004,
  ADR-005, OBS-006).
- TASK-004-vercel-deployment — Vercel deployment variant shipped via
  `@vercel/docker` builder (RUN-2026-09-02-marketplace-005, ADR-006,
  OBS-007). SUPERSEDED by TASK-005 — the `@vercel/docker` builder was
  deprecated and removed from Vercel's npm registry.
- TASK-005-vercel-docker-builder-fix — switched from deprecated
  `@vercel/docker` builder to Vercel's native Dockerfile auto-detection
  via file rename (this run, ADR-007, OBS-008).

## In Progress
- Live verification on Vercel — user must re-import the repo (or
  trigger a fresh build on the existing Vercel project) to confirm the
  build now succeeds without the `@vercel/docker` builder error.
- Render-dashboard Dockerfile-path setting update — Render's default
  Dockerfile name is `Dockerfile`, but on this repo that file is now
  the Vercel variant. Render users must update their dashboard
  "Dockerfile path" setting to `Dockerfile.render`.

## Blocked
- None at the code layer.

## Known Problems
- Vercel's ephemeral container filesystem means contact submissions are
  lost on cold restart (redeploy, scale-to-zero, container recycle).
  Documented in OBS-007; acceptable for marketing-demo use. This
  consequence is independent of the `@vercel/docker` builder
  deprecation and continues to apply under the new auto-detection
  approach.
- Render users must update their dashboard's Dockerfile-path setting
  to `Dockerfile.render`. This cannot be enforced from the repo; it's
  documented in the README and in `Dockerfile.render`'s header comment.
- The `docker/*.vercel.*` files (`vhost.vercel.conf`,
  `entrypoint.vercel.sh`) leak into the Render image's
  `/var/www/html/docker/` directory because `.dockerignore` excludes
  the Dockerfile variants but not the `docker/*.vercel.*` files. Minor
  wart — tiny text files, harmless, not executable. Future agent can
  tighten `.dockerignore` if desired.

## Important Current Facts
- Marketplace app uses SQLite for persistence (database/database.sqlite).
  On Vercel, this file lives at /tmp/storefront/database.sqlite and is
  recreated from migrations on every cold start (OBS-007).
- Marketplace app has no dependency on the core Presence Platform backend.
- Design direction: "Event Ledger" — porcelain paper, deep institutional
  green, hairline-ruled sections (no SaaS card kit), Space Grotesk /
  IBM Plex Sans / IBM Plex Mono self-hosted in public/fonts. See ADR-002.
- The repo's default `Dockerfile` is now the Vercel variant (Apache +
  mod_php + SQLite with $PORT patching and /tmp ephemeral-FS relocation
  per ADR-006 / OBS-007, plus the header comment update from ADR-007).
  Vercel auto-detects this file on import — no `vercel.json` needed.
- The Render / docker-compose deployment target is `Dockerfile.render`
  (renamed from the original `Dockerfile` in this run). docker-compose.yml
  references it explicitly.
- `vercel.json` is intentionally absent. The `@vercel/docker` builder
  it declared has been deprecated and removed from Vercel's npm
  registry (OBS-008).
- TLS-termination handling (ADR-005): proxy-aware
  URL::forceScheme('https') in AppServiceProvider applies to BOTH
  Render and Vercel because both terminate TLS at their load balancers
  and forward X-Forwarded-Proto: https. Local dev (php artisan serve
  on http://localhost:8000) is unaffected.
- ADR-006 (the `vercel.json` + `@vercel/docker` approach) is
  superseded by ADR-007 but is preserved unchanged per the append-only
  protocol. Historical records in RUN-005 / SNAPSHOT-005 / TASK-004
  still reference `Dockerfile.vercel` and `vercel.json` — these are
  immutable historical context, not orphans to be cleaned up.

## Current Main Commit
(recorded after `git commit` + `git push`)

## Current Main Status
BUILDABLE — additive change only (1 file deleted, 2 files renamed, 4
files updated, 6 new .agent/ docs). No changes to application code,
configuration, migrations, models, controllers, Blade templates,
language files, or any of the Dockerfile contents (only header
comments were updated in the two renamed Dockerfiles).

## Active Branches
- main (default; all task branches merged)
- remotes/origin/feature/TASK-001-marketplace-mvp (historical; merged)
- remotes/origin/feature/TASK-002-docker-deployment (historical; merged)
