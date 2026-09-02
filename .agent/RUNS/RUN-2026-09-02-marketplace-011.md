# RUN RUN-2026-09-02-marketplace-011

## Task
TASK-010-vercel-storage-env-overrides.

## Agent Role
Full-Stack Engineer (Laravel / Blade) — Vercel storage and env var fixes.

## Result
PARTIAL — 4 fixes applied and pushed, each resolving a specific crash
point. The last fix (e896c84) should resolve the final `foreach()`
crash, but Vercel live verification is pending user redeploy.

## Resume Notes
- Repository state at resume: clean `main` at commit `1924156` (after
  RUN-010's FrankenPHP switch).
- RUN-010 successfully switched from Apache+mod_php to FrankenPHP,
  but the Vercel deployment still crashed with 500 errors.
- User provided Vercel logs showing FrankenPHP IS running (Server:
  FrankenPHP Caddy, X-Powered-By: PHP/8.4.25) but every request
  returns 500.
- `APP_DEBUG=true` was set in RUN-010, which exposed the actual
  errors across 4 debugging iterations.

## Summary
Debugged a cascade of 4 Vercel runtime crashes, each resolved by a
separate commit:

1. **Empty 500 (bootstrap crash)** — OBS-013: `/app` read-only at
   request time. Fix: override storage paths via env vars (commit
   `51e7b70`).

2. **`ArgumentCountError: Manager::createDriver()`** — OBS-014: the
   `file` maintenance mode driver can't initialize on read-only FS.
   Fix: set `APP_MAINTENANCE_DRIVER=cache` (commit `6874fc7`).

3. **`MissingAppKeyException`** — OBS-015: phpdotenv not loading
   `.env` under FrankenPHP. Fix: export APP_KEY from `.env` (commit
   `2b6b0b7`).

4. **`foreach() argument must be of type array|object, string given`**
   — OBS-015 (continued): translator returning keys as strings because
   `APP_LOCALE` not set. Fix: load ALL env vars from `.env` via
   `set -a; . ./.env; set +a` (commit `e896c84`).

## Changes Made
- `config/view.php` (new) — supports VIEW_COMPILED_PATH env var
- `docker/entrypoint.frankenphp.sh` (updated across 4 commits):
  - Step 1b: `set -a; . ./.env; set +a` — loads ALL env vars
  - Step 2b: unconditional overrides for all file-I/O drivers
  - VIEW_COMPILED_PATH, SESSION_DRIVER, CACHE_STORE,
    APP_MAINTENANCE_DRIVER, APP_MAINTENANCE_STORE, LOG_CHANNEL,
    LOG_STACK, APP_DEBUG, APP_ENV, DB_DATABASE

## Files Changed
- config/view.php (new)
- docker/entrypoint.frankenphp.sh (modified, 4 commits)
- .agent/OBSERVATIONS/OBS-013-vercel-readonly-app-directory-breaks-storage.md (new)
- .agent/OBSERVATIONS/OBS-014-maintenance-mode-file-driver-crash.md (new)
- .agent/OBSERVATIONS/OBS-015-phpdotenv-not-loading-under-frankenphp.md (new)
- .agent/DECISIONS/ADR-012-vercel-storage-env-overrides.md (new)
- .agent/TASKS/TASK-010-vercel-storage-env-overrides.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-011.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-011.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-011.md (new)

## Commits Created
- 51e7b70 — fix(vercel): override storage paths via env vars
- 6874fc7 — fix(vercel): set APP_MAINTENANCE_DRIVER=cache
- 2b6b0b7 — fix(vercel): export APP_KEY as env var
- e896c84 — fix(vercel): load ALL .env vars via set -a

## Branches
- main (direct commits — the debugging was iterative and each commit
  was a coherent fix for a specific crash point)

## Merge Status
- Not applicable — direct commits to main.

## Verification
- `sh -n docker/entrypoint.frankenphp.sh`: PASS (after each commit)
- Static review: PASS
- Vercel live verification: PARTIAL
  - Commit 51e7b70: Vercel confirmed crash moved from empty 500 to
    ArgumentCountError (maintenance mode)
  - Commit 6874fc7: Vercel confirmed crash moved from ArgumentCountError
    to MissingAppKeyException
  - Commit 2b6b0b7: Vercel confirmed crash moved from
    MissingAppKeyException to foreach() on string
  - Commit e896c84: PENDING user redeploy — should resolve the
    foreach() crash by ensuring APP_LOCALE is set

## Discoveries
- OBS-013: Vercel's `/app` is read-only at request time. All file-based
  Laravel drivers crash. Must use env var overrides.
- OBS-014: The `file` maintenance mode driver crashes on read-only FS,
  causing `getDefaultDriver()` to return null → `createDriver()` with
  0 args → ArgumentCountError.
- OBS-015: phpdotenv doesn't load `.env` under FrankenPHP's persistent
  process model. ALL config values that depend on env() return null.
  Fix: `set -a; . ./.env; set +a` at the shell level.

## Decisions
- ADR-012: override ALL storage paths via env vars + load ALL .env vars
  via `set -a`. This is the only reliable approach for Vercel's
  read-only container filesystem.

## Problems / Blockers
- Cannot run Vercel CLI locally — each fix required a user redeploy to
  verify. The user provided error pages/logs after each redeploy, which
  exposed the next crash point.
- `APP_DEBUG=true` is set temporarily — must be changed to `false`
  once the deployment is confirmed working.
- `set -x` in the entrypoint produces verbose Vercel logs — can be
  removed once deployment is confirmed working.

## Remaining Work
- User: redeploy on Vercel and confirm the landing page renders.
- If still crashing: capture the next error from APP_DEBUG=true output.
- Once working: set APP_DEBUG=false, remove set -x from entrypoint.
- Clean up: the old Apache-based Vercel files (docker/apache/vhost.vercel.conf,
  docker/entrypoint.vercel.sh) are unused — can be removed.

## Next Agent Notes
- The entrypoint (`docker/entrypoint.frankenphp.sh`) has `set -a; . ./.env; set +a`
  at step 1b. Do NOT remove this — it's required because phpdotenv doesn't
  load under FrankenPHP (OBS-015).
- The unconditional env var overrides (SESSION_DRIVER=cookie, etc.) at
  step 2b must NOT use `${VAR:-default}` syntax — they must be
  unconditional because `.env` sets them to `file` which would break.
- If the Vercel deployment still crashes, the error will be visible in
  the browser (APP_DEBUG=true). Capture and diagnose.
- Once confirmed working, change APP_DEBUG to false and remove set -x.
- The Render deployment is completely unaffected by all changes in
  this run.
