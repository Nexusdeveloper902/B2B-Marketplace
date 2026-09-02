# STATE SNAPSHOT — RUN-2026-09-02-marketplace-007

## Overall Status
HEALTHY — Vercel runtime DB crash fixed at the code layer; pending
user-side Vercel redeploy to confirm. Render deployment continues to
work unchanged.

## Completed
- TASK-001-marketplace-mvp — five-page bilingual (EN/ES) storefront
  (RUN-2026-09-02-marketplace-002).
- TASK-002-docker-deployment — Apache+mod_php+SQLite Docker image for
  Render (RUN-2026-09-02-marketplace-003, ADR-004).
- TASK-003-render-styling-fix — proxy-aware URL::forceScheme('https')
  for TLS-terminating PaaS proxies (RUN-2026-09-02-marketplace-004,
  ADR-005, OBS-006).
- TASK-004-vercel-deployment — Vercel deployment variant via
  `@vercel/docker` builder (RUN-2026-09-02-marketplace-005, ADR-006,
  OBS-007). SUPERSEDED by TASK-005 — the `@vercel/docker` builder was
  deprecated and removed from Vercel's npm registry.
- TASK-005-vercel-docker-builder-fix — switched from deprecated
  `@vercel/docker` builder to native Dockerfile auto-detection via
  file rename (RUN-2026-09-02-marketplace-006, ADR-007, OBS-008).
- TASK-006-vercel-db-path-fix — always materialize DB_DATABASE into
  .env to fix request-time DB crash (this run, RUN-2026-09-02-
  marketplace-007, ADR-008, OBS-009).

## In Progress
- Live verification on Vercel — user must trigger a fresh Vercel build
  (push to main or hit Redeploy in the Vercel dashboard) and visit the
  deployed URL to confirm the DB crash is resolved.

## Blocked
- None at the code layer.

## Known Problems
- Vercel's ephemeral container filesystem means contact submissions are
  lost on cold restart (redeploy, scale-to-zero, container recycle).
  Documented in OBS-007; acceptable for marketing-demo use. This
  consequence is independent of the DB path mismatch fixed in this run
  and continues to apply.
- If the storage/ symlink step (entrypoint step 1) also fails on Vercel
  due to read-only image layer, this fix alone won't be sufficient.
  The next debug step would be to capture the Vercel build/runtime log
  and look for "permission denied" or "read-only file system" errors
  during the entrypoint's step 1. Not yet observed; flagged as a
  possible next failure mode.
- Render users must update their dashboard's Dockerfile-path setting
  to `Dockerfile.render`. Documented in the README and in
  `Dockerfile.render`'s header comment.

## Important Current Facts
- Marketplace app uses SQLite for persistence (database/database.sqlite).
  On Vercel, the SQLite file lives at /tmp/storefront/database.sqlite
  and is recreated from migrations on every cold start (OBS-007).
  The entrypoint now unconditionally materializes DB_DATABASE into
  .env pointing at that path, so Laravel reads it consistently at
  both migrate-time and request-time (ADR-008, OBS-009).
- Marketplace app has no dependency on the core Presence Platform backend.
- Design direction: "Event Ledger" — porcelain paper, deep institutional
  green, hairline-ruled sections (no SaaS card kit), Space Grotesk /
  IBM Plex Sans / IBM Plex Mono self-hosted in public/fonts. See ADR-002.
- The repo's default `Dockerfile` is the Vercel variant (Apache +
  mod_php + SQLite with $PORT patching and /tmp ephemeral-FS relocation
  per ADR-006 / OBS-007, plus the DB_DATABASE materialization fix from
  ADR-008). Vercel auto-detects this file on import.
- The Render / docker-compose deployment target is `Dockerfile.render`
  (renamed from the original `Dockerfile` in RUN-006). docker-compose.yml
  references it explicitly.
- `vercel.json` is intentionally absent (OBS-008 — the @vercel/docker
  builder is deprecated).
- TLS-termination handling (ADR-005): proxy-aware
  URL::forceScheme('https') in AppServiceProvider applies to BOTH
  Render and Vercel because both terminate TLS at their load balancers
  and forward X-Forwarded-Proto: https.
- The Vercel entrypoint (`docker/entrypoint.vercel.sh`) and the Render
  entrypoint (`docker/entrypoint.sh`) now diverge on the DB_DATABASE
  materialization logic: Vercel unconditionally materializes (ADR-008);
  Render only materializes when the env var is set (per ADR-004 / OBS-005).
  This divergence is intentional and required by the different
  filesystem semantics of the two deployment targets. Future agents
  must NOT "fix" this divergence by making the entrypoints match.

## Current Main Commit
(recorded after `git commit` + `git push`)

## Current Main Status
BUILDABLE — single-file change to `docker/entrypoint.vercel.sh` plus 6
new .agent/ docs. No changes to application code, configuration,
migrations, models, controllers, Blade templates, language files, the
Render Dockerfile, the Render entrypoint, the Render vhost, or any of
the Vercel-specific files beyond the entrypoint.

## Active Branches
- main (default; all task branches merged)
- remotes/origin/feature/TASK-001-marketplace-mvp (historical; merged)
- remotes/origin/feature/TASK-002-docker-deployment (historical; merged)
