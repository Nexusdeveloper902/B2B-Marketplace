# RUN RUN-2026-09-02-marketplace-009

## Task
TASK-008-vercel-services-config.

## Agent Role
Full-Stack Engineer (Laravel / Blade) — Vercel container service config fix.

## Result
COMPLETED (code shipped + pushed; Vercel live verification pending user
redeploy). This run was triggered by the user explicitly requesting deep
research after four failed fix attempts.

## Resume Notes
- Repository state at resume: clean `main` at commit `2709333` (hash
  backfill after RUN-008's config-level fallbacks).
- RUN-005 through RUN-008 all failed to resolve the Vercel crash:
  - RUN-005: `@vercel/docker` builder deprecated (OBS-008)
  - RUN-006: removed `vercel.json`, renamed `Dockerfile.vercel` → `Dockerfile`
  - RUN-007: `.env` materialization fix (ADR-008)
  - RUN-008: config-level fallbacks (ADR-009)
- The user reported the SAME `DatabaseManager.php line 226` error across
  ALL four runs, with different deployment IDs — meaning the code changes
  WERE being deployed but had ZERO effect on the runtime behavior.

## Summary
Performed deep research using web-search and web-reader skills to study
Vercel's official documentation. Discovered that Vercel does NOT
auto-detect a bare `Dockerfile` as a container deployment — it requires
`Dockerfile.vercel` + a `vercel.json` with `services` declaring
`runtime: "container"` + a `rewrites` catch-all.

This is the actual root cause of ALL previous failures: Vercel was never
running the Docker image. It was falling back to framework auto-detection
(detecting Laravel via `composer.json`) and using its own PHP runtime,
completely ignoring the Dockerfile, entrypoint, and config changes.

Fix: reverted RUN-006's file rename (`Dockerfile.vercel` is back, `Dockerfile`
is the Render variant again), created a new `vercel.json` with the correct
`services` + `runtime: "container"` + `rewrites` structure. Kept all
config-level fallbacks (ADR-009) and `.env` materialization (ADR-008) as
secondary layers — they'll now actually take effect.

## Changes Made
- Renamed `Dockerfile` → `Dockerfile.vercel` (undoing RUN-006)
- Renamed `Dockerfile.render` → `Dockerfile` (undoing RUN-006)
- Created `vercel.json` with `services` + `runtime: "container"` + `rewrites`
- Updated `docker-compose.yml` to reference `Dockerfile`
- Updated `.dockerignore` for new filenames
- Updated README with correct Vercel deployment instructions
- Updated both Dockerfiles' header comments

## Files Changed
- Dockerfile.vercel (renamed from Dockerfile; header updated)
- Dockerfile (renamed from Dockerfile.render; header updated)
- vercel.json (new — the correct services config)
- docker-compose.yml (modified — references Dockerfile)
- .dockerignore (modified — new filenames)
- README.md (modified — correct Vercel instructions)
- .agent/OBSERVATIONS/OBS-011-vercel-requires-dockerfile-vercel-and-services-config.md (new)
- .agent/DECISIONS/ADR-010-vercel-services-runtime-container.md (new)
- .agent/TASKS/TASK-008-vercel-services-config.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-009.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-009.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-009.md (new)

## Commits Created
- (hash recorded after `git commit`)

## Branches
- main (direct commit)

## Merge Status
- Not applicable — direct commit to main.

## Verification
- `python3 -c "import json; json.load(open('vercel.json'))"`: PASS
- `sh -n docker/entrypoint.vercel.sh`: PASS
- Static review of file renames: PASS
- Static review of vercel.json against Vercel official docs: PASS
- Vercel live verification: PENDING user redeploy

## Discoveries
- OBS-011: Vercel requires `Dockerfile.vercel` (not `Dockerfile`) +
  `vercel.json` with `services` + `runtime: "container"` + `rewrites`.
  Without this, Vercel falls back to framework auto-detection and the
  Dockerfile is never used. This is why ALL previous fixes had zero effect.

## Decisions
- ADR-010: use `Dockerfile.vercel` + `services` + `runtime: "container"`.
  Supersedes ADR-007 (which incorrectly assumed Vercel auto-detects a
  bare `Dockerfile`).

## Problems / Blockers
- Cannot run Vercel CLI locally — verification pending user redeploy.

## Remaining Work
- User: trigger a fresh Vercel build (push already done).
- User: visit the deployed URL and confirm the styled UI renders.
- User: visit `/__debug` to confirm DB path and storage writability.

## Next Agent Notes
- The `vercel.json` with `services` + `runtime: "container"` is REQUIRED.
  Do NOT remove it.
- The Dockerfile MUST be named `Dockerfile.vercel` (not `Dockerfile`).
  Do NOT rename it.
- The `rewrites` catch-all is required — without it, Vercel doesn't route
  traffic to the container service.
- If the Vercel deployment still crashes after this fix, visit `/__debug`
  on the deployed URL — it will show the DB path, file writability, and
  storage writability, which will pinpoint any remaining issues.
- The config-level fallbacks (ADR-009) and `.env` materialization (ADR-008)
  from previous runs are kept as secondary layers — they'll now actually
  take effect since Vercel will be running the Docker image.
