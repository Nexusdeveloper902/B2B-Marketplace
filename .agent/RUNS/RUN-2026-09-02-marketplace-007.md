# RUN RUN-2026-09-02-marketplace-007

## Task
TASK-006-vercel-db-path-fix.

## Agent Role
Full-Stack Engineer (Laravel / Blade) — Vercel runtime crash fix.

## Result
COMPLETED (code shipped + pushed; Vercel live verification pending user
redeploy).

## Resume Notes
- Repository state at resume: clean `main` at commit a086674 (hash
  backfill after RUN-006's @vercel/docker builder deprecation fix).
- RUN-006 successfully fixed the build-time failure (Vercel could now
  build the image), but the runtime crashed on every request with
  `DatabaseManager.php line 226`.
- User-provided Vercel logs on 2026-09-02 showed:
  - 500 INTERNAL_SERVER_ERROR on /, /favicon.ico, /favicon.png
  - "In DatabaseManager.php line 226:" (info level, request-time)
  - "Application exited with code 1." (error level, container dying)
- Diagnosis (OBS-009): path mismatch between where the entrypoint
  creates the SQLite file (/tmp/storefront/database.sqlite) and where
  Laravel looks for it at request time (/var/www/html/database/database.sqlite,
  via Laravel's `database_path('database.sqlite')` fallback when
  DB_DATABASE is unset in .env).

## Summary
The Vercel entrypoint's `DB_DATABASE` materialization was gated on
`if [ -n "${DB_DATABASE:-}" ]`, which only fired when the env var was
explicitly set. On Vercel no such env var is set, so .env kept its
commented-out `# DB_DATABASE=laravel` line and Laravel used the default
path — which is in the read-only image layer at request time, crashing
the DB connection.

Fix: removed the guard and always materialize `DB_DATABASE=$DB_FILE`
into `.env`, where `$DB_FILE` is the `/tmp/storefront/database.sqlite`
path the entrypoint just created and migrated. Both migrate-time (CLI)
and request-time (Apache mod_php) now read the same path.

The Render entrypoint is unchanged — its conditional materialization
works on Render because `/var/www/html/database/` is writable there at
request time. The two entrypoints now intentionally diverge on this
point (ADR-008).

## Changes Made
- Modified `docker/entrypoint.vercel.sh`:
  - Removed the `if [ -n "${DB_DATABASE:-}" ]` guard around the
    DB_DATABASE materialization block.
  - Changed `$DB_DATABASE` to `$DB_FILE` in the sed/printf so the
    materialized value is the resolved path, not the (possibly-unset)
    env var.
  - Added explanatory comment referencing OBS-009 and ADR-008.
- Added `.agent/OBSERVATIONS/OBS-009-vercel-db-path-mismatch.md`.
- Added `.agent/DECISIONS/ADR-008-vercel-db-path-materialization.md`.
- Added `.agent/TASKS/TASK-006-vercel-db-path-fix.md`.
- Added this run record + its activity ledger.
- Added `.agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-007.md`.

## Files Changed
- docker/entrypoint.vercel.sh (modified)
- .agent/OBSERVATIONS/OBS-009-vercel-db-path-mismatch.md (new)
- .agent/DECISIONS/ADR-008-vercel-db-path-materialization.md (new)
- .agent/TASKS/TASK-006-vercel-db-path-fix.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-007.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-007.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-007.md (new)

## Commits Created
- e422ae6 — fix(vercel): always materialize DB_DATABASE into .env to fix request-time DB crash

## Branches
- main (direct commit — single coherent fix-the-Vercel-runtime-crash
  unit of work, no feature branch warranted)

## Merge Status
- Not applicable — committed directly to main. No feature branch.

## Verification
- `sh -n docker/entrypoint.vercel.sh`: PASS (shell syntax check).
- Static review of the changed block: PASS — materialization now fires
  unconditionally and uses `$DB_FILE` (the resolved path) instead of
  `$DB_DATABASE` (the env var, possibly unset).
- Static review confirms the Render entrypoint (`docker/entrypoint.sh`)
  is unchanged: PASS.
- Static review confirms `config/database.php` is unchanged: PASS.
- Static review confirms no other files were modified: PASS.
- Vercel live verification: PENDING (no Vercel CLI or Docker daemon in
  this environment; user must trigger a fresh Vercel build).

## Discoveries
- OBS-009: Vercel's container runtime makes `/var/www/html/` read-only
  (or inaccessible to `www-data`) at request time, even though the
  entrypoint can write to it during container startup. This means
  Laravel's default SQLite path (`database_path('database.sqlite')`
  = `/var/www/html/database/database.sqlite`) is unreachable at request
  time on Vercel, even if the entrypoint successfully migrated against
  it at startup. The fix is to force Laravel to use `/tmp/storefront/database.sqlite`
  via unconditional `DB_DATABASE` materialization in `.env`.
- The Render entrypoint's conditional materialization works on Render
  because Render's container filesystem is writable at request time.
  The two entrypoints now intentionally diverge on this point.

## Decisions
- ADR-008: always materialize `DB_DATABASE=$DB_FILE` into `.env` in the
  Vercel entrypoint. Rejected alternatives: set `DB_DATABASE` as a
  Vercel dashboard env var (requires user setup, may not reach mod_php
  per OBS-005), change `config/database.php` to default to /tmp
  (pollutes app config, breaks local dev), make the Render entrypoint
  also unconditionally materialize (would regress Render), symlink the
  default path to /tmp (unnecessary indirection).

## Problems / Blockers
- Cannot run Vercel CLI locally — Vercel live verification must be done
  by the user via redeploy.
- If the storage/ symlink step (entrypoint step 1) also fails on Vercel
  due to read-only image layer, this fix alone won't be sufficient.
  The next debug step would be to capture the Vercel build/runtime log
  and look for "permission denied" or "read-only file system" errors
  during the entrypoint's step 1.

## Remaining Work
- User: trigger a fresh Vercel build (push to main or hit Redeploy in
  the Vercel dashboard). The DB crash should be resolved.
- User: visit the deployed URL and confirm:
  1. The landing page (/) renders with the "Event Ledger" styling.
  2. The contact form (/contact) submits successfully.
  3. Hard-refresh to bypass cached 500 response.
- If the Vercel deployment still crashes, capture the Vercel runtime
  log and look for entrypoint-step-1 (storage symlink) failures.

## Next Agent Notes
- The fix is in `docker/entrypoint.vercel.sh` step 3b. Do NOT remove
  the unconditional materialization without confirming Vercel's
  filesystem behavior has changed.
- Do NOT make the Render entrypoint (`docker/entrypoint.sh`) match the
  Vercel entrypoint's unconditional materialization — the divergence is
  intentional (ADR-008). Render's conditional materialization works
  because Render's filesystem is writable at request time.
- Do NOT modify `config/database.php` to hardcode `/tmp/...` paths —
  application config should not carry deployment-target-specific paths.
  The `.env` materialization is the Laravel-idiomatic override.
- If the user reports the Vercel deployment still crashes after this
  fix, the next most likely cause is the storage/ symlink (entrypoint
  step 1) failing because `/var/www/html` is read-only even for the
  entrypoint user. Look for "permission denied" or "read-only file
  system" errors in the Vercel build/runtime log.
- If a future agent moves the Vercel deployment to Vercel Persistent
  Storage, the entrypoint would need `DB_FILE` to point at the mounted
  volume path instead of `/tmp/storefront/database.sqlite`. The
  unconditional materialization would still be correct — it would
  just materialize a different path.
