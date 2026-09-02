# Activity Ledger — RUN-2026-09-02-marketplace-011

## Resume investigation
- ACTION: re-establish repo state
- COMMAND: git config core.fileMode false && git pull --ff-only origin main
- RESULT: clean main at 1924156 (after RUN-010's FrankenPHP switch).

## Receive user-reported Vercel logs (empty 500s)
- USER REPORT: Vercel deployment still returning 500 on every request.
  Logs showed FrankenPHP IS running (Server: FrankenPHP Caddy,
  X-Powered-By: PHP/8.4.25) but every request returns 500 with
  size:0 (empty body) and 15-70ms duration (bootstrap crash).
- DIAGNOSIS (OBS-013): /app is read-only at request time on Vercel.
  The storage/ symlink approach fails silently. Laravel crashes
  because it can't write compiled views, sessions, cache, or logs.

## Commit 1: storage path env var overrides
- FILE: config/view.php (new), docker/entrypoint.frankenphp.sh
- COMMIT: 51e7b70
- CHANGES: VIEW_COMPILED_PATH=/tmp/..., SESSION_DRIVER=cookie,
  CACHE_STORE=array, LOG_CHANNEL=stderr, APP_DEBUG=true
- VERIFICATION: sh -n PASS; Vercel redeploy confirmed crash moved
  from empty 500 to ArgumentCountError (maintenance mode)

## Commit 2: maintenance mode fix
- FILE: docker/entrypoint.frankenphp.sh
- COMMIT: 6874fc7
- CHANGES: APP_MAINTENANCE_DRIVER=cache, APP_MAINTENANCE_STORE=array
- DIAGNOSIS (OBS-014): the file maintenance driver crashes on
  read-only FS, causing getDefaultDriver() to return null.
- VERIFICATION: sh -n PASS; Vercel confirmed crash moved from
  ArgumentCountError to MissingAppKeyException

## Commit 3: APP_KEY export
- FILE: docker/entrypoint.frankenphp.sh
- COMMIT: 2b6b0b7
- CHANGES: extract APP_KEY from .env and export as shell env var
- DIAGNOSIS (OBS-015): phpdotenv not loading .env under FrankenPHP.
  config('app.key') returns null.
- VERIFICATION: sh -n PASS; Vercel confirmed crash moved from
  MissingAppKeyException to foreach() on string (translation)

## Commit 4: load ALL .env vars via set -a
- FILE: docker/entrypoint.frankenphp.sh
- COMMIT: e896c84
- CHANGES: `set -a; . ./.env; set +a` at step 1b (before overrides);
  made all overrides unconditional (not ${VAR:-default})
- DIAGNOSIS (OBS-015 continued): APP_LOCALE also not set, causing
  translator to return keys as strings instead of arrays.
- VERIFICATION: sh -n PASS; static review PASS; Vercel PENDING

## Documentation
- FILES CREATED:
  - .agent/OBSERVATIONS/OBS-013-vercel-readonly-app-directory-breaks-storage.md
  - .agent/OBSERVATIONS/OBS-014-maintenance-mode-file-driver-crash.md
  - .agent/OBSERVATIONS/OBS-015-phpdotenv-not-loading-under-frankenphp.md
  - .agent/DECISIONS/ADR-012-vercel-storage-env-overrides.md
  - .agent/TASKS/TASK-010-vercel-storage-env-overrides.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-011.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-011.ledger.md (this file)
  - .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-011.md
- RESULT: SUCCESS — all append-only records created per protocol
  Sections 11-17. No historical records modified or overwritten.

## Push
- All 4 commits already pushed to origin/main during the debugging
  session. This documentation commit is the final step.
