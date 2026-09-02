# TASK-008-vercel-services-config

## Objective
Fix the persistent Vercel runtime crash (`DatabaseManager.php line 226`)
that survived FOUR previous fix attempts by correctly configuring Vercel's
container service via `Dockerfile.vercel` + `vercel.json` with `services`
+ `runtime: "container"` + `rewrites`.

## Origin
User-reported persistent Vercel crash on 2026-09-02 after RUN-005 through
RUN-008. User explicitly requested deep research because "even after
those two previous attempts I am still getting exactly the same issue
with exactly the same logs and different deployments."

## Root Cause
Vercel does NOT auto-detect a bare `Dockerfile` as a container deployment.
A bare `Dockerfile` is treated as a generic serverless function (with
different semantics). To deploy a Docker image as a proper container
service on Vercel, you need ALL THREE of:
1. The Dockerfile named `Dockerfile.vercel` (not `Dockerfile`)
2. A `vercel.json` with `services` declaring `runtime: "container"`
3. A `rewrites` catch-all routing traffic to the service

Without this, Vercel falls back to framework auto-detection (detecting
Laravel via `composer.json`) and the Dockerfile is never used. This is
why all previous fixes had zero effect. See OBS-011 for full evidence.

## Implementation
1. Renamed `Dockerfile` → `Dockerfile.vercel` (undoing RUN-006)
2. Renamed `Dockerfile.render` → `Dockerfile` (undoing RUN-006)
3. Created new `vercel.json` with `services` + `runtime: "container"` + `rewrites`
4. Updated `docker-compose.yml` to reference `Dockerfile`
5. Updated `.dockerignore` for new filenames
6. Updated README with correct Vercel deployment instructions
7. Kept all config-level fallbacks (ADR-009) and `.env` materialization
   (ADR-008) as secondary layers — they'll now actually take effect

## Acceptance Criteria
- [x] `Dockerfile.vercel` exists (Vercel detects this filename)
- [x] `vercel.json` has `services` with `runtime: "container"` + `rewrites`
- [x] `Dockerfile` is the Render variant (repo's default)
- [x] `docker-compose.yml` references `Dockerfile`
- [x] `.dockerignore` excludes both variants from each other's context
- [x] README documents the correct Vercel deployment flow
- [x] ADR-010 + OBS-011 + TASK-008 + RUN-009 + ledger + snapshot written
- [x] Committed and pushed

## Commits

### Commit — {{COMMIT_HASH}}
Date: 2026-09-02
Branch: main

Summary:
fix(vercel): use Dockerfile.vercel + services runtime:container (the actual fix)

Deep research into Vercel's official docs revealed that Vercel does NOT
auto-detect a bare `Dockerfile` as a container deployment. A bare
`Dockerfile` is treated as a generic serverless function. To run a Docker
image as a proper container service, Vercel requires:
1. The Dockerfile named `Dockerfile.vercel` (not `Dockerfile`)
2. A `vercel.json` with `services` declaring `runtime: "container"`
3. A `rewrites` catch-all routing traffic to the service

This is why all previous fixes (RUN-006 through RUN-008) had zero effect —
Vercel was never running the Docker image; it was falling back to
framework auto-detection (detecting Laravel via composer.json).

Fix:
- Renamed `Dockerfile` → `Dockerfile.vercel` (undoing RUN-006's rename)
- Renamed `Dockerfile.render` → `Dockerfile` (undoing RUN-006's rename)
- Created `vercel.json` with `services` + `runtime: "container"` + `rewrites`
- Updated `docker-compose.yml`, `.dockerignore`, README

The config-level fallbacks (ADR-009) and `.env` materialization (ADR-008)
from previous runs are kept as secondary layers — they'll now actually
take effect since Vercel will be running the Docker image.

See ADR-010, OBS-011.
