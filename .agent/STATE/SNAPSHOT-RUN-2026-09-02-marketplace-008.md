# STATE SNAPSHOT — RUN-2026-09-02-marketplace-008

## Overall Status
HEALTHY — config-level fallbacks shipped; pending user-side Vercel
redeploy to confirm.

## Completed
- TASK-001 through TASK-006 (prior runs).
- TASK-007-vercel-config-level-fallbacks (this run, ADR-009, OBS-010).

## In Progress
- Live verification on Vercel — user must redeploy and visit `/__debug`.

## Blocked
- None at the code layer.

## Known Problems
- Vercel's ephemeral filesystem means contact submissions are lost on
  cold restart (OBS-007).
- The `/__debug` route is unauthenticated — remove before production.

## Important Current Facts
- config/database.php: SQLite path falls back to /tmp/storefront/database.sqlite
  when the default directory (/var/www/html/database/) isn't writable (Vercel).
- config/session.php: default session driver is now `file` (was `database`).
- config/cache.php: default cache store is now `file` (was `database`).
- routes/web.php: `/__debug` route outputs DB/storage/extension diagnostics.
- docker/entrypoint.vercel.sh: `set -x` trace + echo diagnostics, non-fatal
  storage symlink + migrate.
- Render deployment is unaffected by these config changes (Render's
  filesystem is writable, so the fallback doesn't kick in; the defaults
  now match what .env was already setting).

## Current Main Commit
(recorded after commit + push)

## Current Main Status
BUILDABLE — config-level + entrypoint changes, no application code
or migration changes.

## Active Branches
- main (default)
