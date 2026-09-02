# Activity Ledger — RUN-2026-09-02-marketplace-007

## Resume investigation
- ACTION: re-establish repo state from prior runs
- COMMAND: git config core.fileMode false && git pull --ff-only origin main
- RESULT: clean main at a086674.

## Receive user-reported Vercel runtime crash
- USER REPORT on 2026-09-02:
  ```
  500: INTERNAL_SERVER_ERROR
  Code: FUNCTION_INVOCATION_FAILED

  In DatabaseManager.php line 226:
  Application exited with code 1.
  ```
  Plus 7 Vercel log entries showing 500s on /, /favicon.ico, /favicon.png.
- DIAGNOSIS: path mismatch between entrypoint-created SQLite file
  (/tmp/storefront/database.sqlite) and Laravel's request-time fallback
  path (/var/www/html/database/database.sqlite, via Laravel's
  database_path('database.sqlite') when DB_DATABASE is unset in .env).
  Recorded in OBS-009.

## Recover prior task documentation
- FILES:
  - .agent/TASKS/TASK-005-vercel-docker-builder-fix.md (completed in
    RUN-006, fixed the @vercel/docker builder deprecation)
  - .agent/DECISIONS/ADR-007-vercel-default-dockerfile.md (Dockerfile
    auto-detection rename)
  - .agent/OBSERVATIONS/OBS-008-vercel-docker-builder-deprecated.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-006.md
- RESULT: FOUND — RUN-006 fixed the build-time failure; this run fixes
  the runtime crash that surfaced after the build succeeded.

## Diagnose
- INSPECTION:
  - docker/entrypoint.vercel.sh lines 86-92 (pre-fix): the
    `if [ -n "${DB_DATABASE:-}" ]` guard caused DB_DATABASE
    materialization to be skipped when the env var was unset (which
    is the case on Vercel).
  - config/database.php line 38: `'database' => env('DB_DATABASE',
    database_path('database.sqlite'))` — confirms Laravel's fallback
    path is /var/www/html/database/database.sqlite when DB_DATABASE
    is unset.
  - The shipped .env has `# DB_DATABASE=laravel` (commented out), so
    Laravel uses the fallback path.
- DIAGNOSIS: at request time, Laravel tries to open
  /var/www/html/database/database.sqlite. On Vercel, that path is in
  the read-only image layer (or inaccessible to www-data), so SQLite
  PDO fails with "unable to open database file", surfacing as a
  DatabaseManager exception at line 226.

## Apply fix
- FILE: docker/entrypoint.vercel.sh
- CHANGE: removed the `if [ -n "${DB_DATABASE:-}" ]` guard around the
  DB_DATABASE materialization block; changed `$DB_DATABASE` to
  `$DB_FILE` in the sed/printf so the materialized value is the
  resolved /tmp path. Added explanatory comment referencing OBS-009
  and ADR-008.
- DESIGN RECORDED IN: ADR-008.

## Verify
- ACTION: shell syntax check on entrypoint
- COMMAND: sh -n docker/entrypoint.vercel.sh
- RESULT: PASS (SYNTAX OK)
- ACTION: static review of changed block
- RESULT: PASS — materialization now fires unconditionally and uses
  $DB_FILE (the resolved path) instead of $DB_DATABASE (the env var,
  possibly unset).
- ACTION: confirm Render entrypoint unchanged
- COMMAND: git diff docker/entrypoint.sh
- RESULT: PASS — no changes to Render entrypoint.
- ACTION: confirm config/database.php unchanged
- COMMAND: git diff config/database.php
- RESULT: PASS — no changes to application config.
- ACTION: confirm no other files modified
- COMMAND: git status --short (excluding new .agent/ files)
- RESULT: PASS — only docker/entrypoint.vercel.sh modified.
- ACTION: Vercel live build verification
- RESULT: PENDING — no Vercel CLI or Docker daemon in this environment.

## Document
- FILES:
  - .agent/OBSERVATIONS/OBS-009-vercel-db-path-mismatch.md
  - .agent/DECISIONS/ADR-008-vercel-db-path-materialization.md
  - .agent/TASKS/TASK-006-vercel-db-path-fix.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-007.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-007.ledger.md (this file)
  - .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-007.md
- RESULT: SUCCESS — all append-only records created per protocol
  Sections 11-17. No historical records modified or overwritten.

## Commit
- ACTION: commit fix + .agent/ docs to main
- BRANCH: main
- MESSAGE: fix(vercel): always materialize DB_DATABASE into .env to fix
  request-time DB crash
- RESULT: (hash recorded in TASK-006 commit entry after git commit)

## Push
- ACTION: push main to origin
- COMMAND: git push origin main
- RESULT: (recorded after push)
