# TASK-010-vercel-storage-env-overrides

## Objective
Fix the cascade of Vercel runtime crashes that persisted after the
FrankenPHP switch (ADR-011). The deployment was running FrankenPHP
but crashing during Laravel's bootstrap due to Vercel's read-only
container filesystem and phpdotenv not loading `.env`.

## Origin
User-reported Vercel errors across 4 debugging iterations (commits
`51e7b70`, `6874fc7`, `2b6b0b7`, `e896c84`), each exposing a new
crash point:
1. Empty 500 responses (bootstrap crash, no error visible)
2. `ArgumentCountError: Manager::createDriver()` (maintenance mode)
3. `MissingAppKeyException` (phpdotenv not loading .env)
4. `foreach() argument must be of type array|object, string given`
   (translator returning keys as strings due to missing APP_LOCALE)

## Root Causes
- OBS-013: `/app` is read-only at request time on Vercel. File-based
  storage drivers crash.
- OBS-014: The `file` maintenance mode driver can't initialize on
  read-only FS → `getDefaultDriver()` returns null → crash.
- OBS-015: phpdotenv doesn't load `/app/.env` under FrankenPHP's
  persistent process model → all `env()` calls return null → config
  resolution fails.

## Implementation
1. **`config/view.php`** (new) — explicitly supports `VIEW_COMPILED_PATH`
   env var.
2. **`docker/entrypoint.frankenphp.sh`** (updated):
   - Step 1b: `set -a; . ./.env; set +a` — loads ALL env vars from .env
   - Step 2b: Unconditional overrides for SESSION_DRIVER=cookie,
     CACHE_STORE=array, APP_MAINTENANCE_DRIVER=cache,
     APP_MAINTENANCE_STORE=array, LOG_CHANNEL=stderr,
     VIEW_COMPILED_PATH=/tmp/..., APP_DEBUG=true, DB_DATABASE=/tmp/...

## Commits

### Commit — 51e7b70
Date: 2026-09-02
Branch: main

Summary: fix(vercel): override storage paths via env vars (read-only filesystem fix)
Changes: config/view.php (new), docker/entrypoint.frankenphp.sh (env var exports)
Verification: sh -n entrypoint PASS; static review PASS; Vercel live PENDING

### Commit — 6874fc7
Date: 2026-09-02
Branch: main

Summary: fix(vercel): set APP_MAINTENANCE_DRIVER=cache to fix bootstrap crash
Changes: docker/entrypoint.frankenphp.sh (APP_MAINTENANCE_DRIVER + STORE exports)
Verification: sh -n PASS; Vercel confirmed error changed to MissingAppKeyException

### Commit — 2b6b0b7
Date: 2026-09-02
Branch: main

Summary: fix(vercel): export APP_KEY as env var (bypass phpdotenv loading issue)
Changes: docker/entrypoint.frankenphp.sh (APP_KEY extraction from .env)
Verification: sh -n PASS; Vercel confirmed error moved to foreach() crash

### Commit — e896c84
Date: 2026-09-02
Branch: main

Summary: fix(vercel): load ALL .env vars via set -a (fix translation loader + APP_KEY)
Changes: docker/entrypoint.frankenphp.sh (set -a + . ./.env; unconditional overrides)
Verification: sh -n PASS; static review PASS; Vercel live PENDING

## Acceptance Criteria
- [x] Storage paths redirected via env vars (VIEW_COMPILED_PATH, etc.)
- [x] Maintenance mode driver set to cache (not file)
- [x] ALL .env vars loaded via `set -a; . ./.env; set +a`
- [x] File-I/O drivers unconditionally overridden (cookie, array, stderr)
- [x] config/view.php added for VIEW_COMPILED_PATH support
- [x] ADR-012 + OBS-013/014/015 + TASK-010 + RUN-011 + ledger + snapshot written
- [x] Committed and pushed

## Remaining Work
- User: redeploy on Vercel. The last fix (e896c84) should resolve the
  foreach() crash by ensuring APP_LOCALE is set.
- If still crashing, capture the next error from APP_DEBUG=true output.
- Once confirmed working: set APP_DEBUG=false, remove set -x from entrypoint.
