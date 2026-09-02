# RUN RUN-2026-09-02-marketplace-008

## Task
TASK-007-vercel-config-level-fallbacks.

## Agent Role
Full-Stack Engineer (Laravel / Blade) — Vercel config-level fallbacks.

## Result
COMPLETED (code shipped + pushed; Vercel live verification pending user
redeploy).

## Resume Notes
- Repository state at resume: clean `main` at commit `ccc2116` (hash
  backfill after RUN-007's `.env` materialization fix).
- RUN-007's fix (ADR-008) was correctly pushed and Vercel rebuilt the
  image (new deployment ID `dpl_5szrpPS4WyTZTjqdYs5xEy7to2zh`), but
  the runtime crash persisted — same `DatabaseManager.php line 226`
  error on every request.
- Diagnosis (OBS-010): Vercel's filesystem is likely read-only for the
  entrypoint's `sed -i .env`, so the materialization had no effect.
  AND Laravel's config defaults use `database` for session/cache,
  which triggers a DB connection on every request (via session
  middleware) — explaining why even `/favicon.ico` crashes.

## Summary
Moved deployment-target-specific fallbacks from the entrypoint (runtime
`.env` modification) to the config files (code-level, no `.env`
dependency). Changed session/cache defaults from `database` to `file`.
Added a code-level SQLite path fallback in `config/database.php`. Added
a `/__debug` route for diagnostics. Hardened the entrypoint with `set -x`
trace mode, non-fatal storage symlink, and non-fatal migrate.

## Changes Made
- `config/database.php`: code-level SQLite path fallback to /tmp.
- `config/session.php`: default `database` → `file`.
- `config/cache.php`: default `database` → `file`.
- `routes/web.php`: added `/__debug` route.
- `docker/entrypoint.vercel.sh`: diagnostics + non-fatal steps.

## Files Changed
- config/database.php (modified)
- config/session.php (modified)
- config/cache.php (modified)
- routes/web.php (modified)
- docker/entrypoint.vercel.sh (modified)
- .agent/OBSERVATIONS/OBS-010-vercel-readonly-filesystem-breaks-env-override.md (new)
- .agent/DECISIONS/ADR-009-vercel-config-level-fallbacks.md (new)
- .agent/TASKS/TASK-007-vercel-config-level-fallbacks.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-008.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-008.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-008.md (new)

## Commits Created
- (hash recorded after `git commit`)

## Branches
- main (direct commit)

## Merge Status
- Not applicable — direct commit to main.

## Verification
- `sh -n docker/entrypoint.vercel.sh`: PASS.
- Basic PHP structure check (balanced braces, open tags): PASS.
- Static review of config changes: PASS.
- Vercel live verification: PENDING user redeploy.

## Discoveries
- OBS-010: Vercel's filesystem may be read-only for the entrypoint's
  `sed -i .env`. AND Laravel's config defaults use `database` for
  session/cache, which triggers a DB connection on every request.

## Decisions
- ADR-009: config-level fallbacks (code-level, no .env dependency).
  ADR-008 (.env materialization) remains ACTIVE as secondary layer.

## Problems / Blockers
- Cannot run Vercel CLI locally — verification pending user redeploy.

## Remaining Work
- User: redeploy on Vercel. Visit `/__debug` to confirm the DB path
  and storage writability. Visit `/` to confirm the styled UI renders.

## Next Agent Notes
- The config-level fallbacks in `config/database.php`, `config/session.php`,
  and `config/cache.php` are the primary fix. Do NOT remove them.
- The `/__debug` route is always available (no auth). Remove or auth-gate
  if the app goes to production with real users.
- The entrypoint's `set -x` trace mode produces verbose Vercel logs.
  Remove `set -x` once the deployment is confirmed working.
- If the Vercel deployment STILL crashes after this fix, visit `/__debug`
  on the deployed URL. The JSON output will show exactly what's wrong
  (DB path, file writability, storage writability, PHP extensions).
