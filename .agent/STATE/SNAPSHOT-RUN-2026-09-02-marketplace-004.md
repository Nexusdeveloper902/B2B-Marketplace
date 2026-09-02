# STATE SNAPSHOT — RUN-2026-09-02-marketplace-004

## Overall Status
HEALTHY — production-styling bug fixed at the code layer; pending user-side
Render redeploy to confirm.

## Completed
- TASK-001-marketplace-mvp — five-page bilingual (EN/ES) storefront, merged
  to main (RUN-2026-09-02-marketplace-002).
- TASK-002-docker-deployment — Apache+mod_php+SQLite Docker image,
  docker-compose, entrypoint, merged to main (RUN-2026-09-02-marketplace-003).
- TASK-003-render-styling-fix — proxy-aware `URL::forceScheme('https')` in
  AppServiceProvider, documented in ADR-005 / OBS-006 (this run).

## In Progress
- Live verification on Render — user must trigger a fresh rebuild (push
  to main already done; Render auto-builds on push) and visit the deployed
  URL to confirm styled rendering.

## Blocked
- None at the code layer.

## Known Problems
- The committed `.env` ships `APP_URL=http://localhost:8000`. This is NOT
  the cause of the styling bug (asset() uses request context, not APP_URL,
  in HTTP requests — see OBS-006). It IS wrong for non-HTTP-context URL
  generation (queue/mail/signed routes), which this app does not currently
  use. If a future agent adds any of those, they must either set APP_URL
  via a Render env var (and extend the entrypoint to materialize it per
  OBS-005) or set ASSET_URL the same way.

## Important Current Facts
- Marketplace app uses SQLite for persistence (database/database.sqlite).
- Marketplace app has no dependency on the core Presence Platform backend.
- Design direction: "Event Ledger" — porcelain paper, deep institutional
  green, hairline-ruled sections (no SaaS card kit), Space Grotesk /
  IBM Plex Sans / IBM Plex Mono self-hosted in public/fonts. See ADR-002.
- Docker: single-container Apache + mod_php + SQLite, no Node, no Vite,
  no external services. See ADR-004.
- Deployment target: Render (TLS-terminating PaaS proxy). Asset URLs
  require `URL::forceScheme('https')` to avoid mixed-content blocking —
  see ADR-005 and OBS-006.
- Local dev: `php artisan serve` on http://localhost:8000 is unaffected
  by the forceScheme fix (no proxy header → no force applied).

## Current Main Commit
(recorded after `git commit` + `git push`)

## Current Main Status
BUILDABLE — single one-line-code change to AppServiceProvider, plus
append-only .agent/ documentation. No dependency changes, no Dockerfile
changes, no migration changes. Verified by static review; full build
verification pending Render-side redeploy.

## Active Branches
- main (default; all task branches merged)
- remotes/origin/feature/TASK-001-marketplace-mvp (historical; merged)
- remotes/origin/feature/TASK-002-docker-deployment (historical; merged)
