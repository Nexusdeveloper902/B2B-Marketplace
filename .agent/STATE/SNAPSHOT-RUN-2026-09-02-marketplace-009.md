# STATE SNAPSHOT — RUN-2026-09-02-marketplace-009

## Overall Status
HEALTHY — Vercel container service config corrected after deep research;
pending user-side Vercel redeploy to confirm. This is the FIFTH attempt
at fixing the Vercel deployment; the previous four failed because Vercel
was never running the Docker image (OBS-011).

## Completed
- TASK-001 through TASK-007 (prior runs).
- TASK-008-vercel-services-config (this run, ADR-010, OBS-011).

## In Progress
- Live verification on Vercel — user must trigger a fresh build.

## Blocked
- None at the code layer.

## Known Problems
- Vercel's ephemeral filesystem means contact submissions are lost on
  cold restart (OBS-007). Acceptable for marketing demo.
- The `/__debug` route is unauthenticated — remove before production.

## Important Current Facts
- The repo now has the CORRECT Vercel container service configuration:
  - `Dockerfile.vercel` (Vercel detects this filename)
  - `vercel.json` with `services` + `runtime: "container"` + `rewrites`
- `Dockerfile` is the Render variant (repo's default, used by docker-compose).
- The config-level fallbacks (ADR-009) and `.env` materialization (ADR-008)
  from previous runs are kept as secondary layers — they'll now actually
  take effect since Vercel will be running the Docker image.
- ADR-007 (which assumed Vercel auto-detects a bare `Dockerfile`) is
  SUPERSEDED by ADR-010.
- Render deployment is unaffected — `Dockerfile` is the Render variant,
  docker-compose.yml references it.

## Current Main Commit
(recorded after commit + push)

## Current Main Status
BUILDABLE — file renames + new vercel.json + config updates.

## Active Branches
- main (default)
