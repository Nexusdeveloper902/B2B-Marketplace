# TASK-006-vercel-db-path-fix

## Objective
Fix the Vercel runtime crash (`DatabaseManager.php line 226`,
`FUNCTION_INVOCATION_FAILED`, 500 INTERNAL_SERVER_ERROR on every
request) caused by a path mismatch between where the entrypoint creates
the SQLite database file and where Laravel looks for it at request time.

## Origin
User-reported Vercel log on 2026-09-02:
```
{"level":"info","message":"In DatabaseManager.php line 226:",
 "requestMethod":"GET","requestPath":"/","responseStatusCode":500,...}
{"level":"error","message":"Application exited with code 1.",
 "requestMethod":"GET","requestPath":"/","responseStatusCode":500,...}
```
This was the second Vercel build failure: RUN-006 fixed the
`@vercel/docker` builder deprecation (OBS-008), but the deployment
still crashed at request time. Root cause documented in OBS-009.

## Requirements
- Make Laravel look at the same SQLite file at migrate-time (CLI, in
  the entrypoint) and at request-time (Apache mod_php).
- The file must be at a path that is writable by `www-data` at request
  time, without relying on the `chown` step (which may be skipped if
  Vercel runs the container as non-root).
- Do NOT modify the Render entrypoint (`docker/entrypoint.sh`) — the
  Render deployment is currently working and its conditional
  materialization is correct for Render's filesystem semantics.
- Do NOT modify `config/database.php` — application config should not
  carry deployment-target-specific paths.
- Document the fix in `.agent/OBSERVATIONS/OBS-009-*` and
  `.agent/DECISIONS/ADR-008-*`.

## Acceptance Criteria
- [x] `docker/entrypoint.vercel.sh` unconditionally materializes
      `DB_DATABASE=$DB_FILE` into `.env` (no `if [ -n "${DB_DATABASE:-}" ]`
      guard).
- [x] The materialized path points at `/tmp/storefront/database.sqlite`
      (the file the entrypoint creates in step 2).
- [x] The Render entrypoint (`docker/entrypoint.sh`) is unchanged.
- [x] `sh -n docker/entrypoint.vercel.sh` passes (shell syntax check).
- [x] ADR-008 records the decision and explicitly notes the divergence
      from the Render entrypoint's logic.
- [x] OBS-009 records the root cause and evidence.
- [x] No changes to: `config/database.php`, `Dockerfile`, `Dockerfile.render`,
      `docker/apache/vhost.conf`, `docker/apache/vhost.vercel.conf`,
      `docker/entrypoint.sh`, `docker-compose.yml`, application code,
      migrations, models, controllers, Blade templates, language files.
- [x] Fix is committed to `main` and pushed.

## Implementation
Single-file change to `docker/entrypoint.vercel.sh`:

Before (lines 86-92, pre-fix):
```bash
if [ -n "${DB_DATABASE:-}" ] && [ -f .env ]; then
    if grep -q '^DB_DATABASE=' .env; then
        sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
    else
        printf '\nDB_DATABASE=%s\n' "$DB_DATABASE" >> .env
    fi
fi
```

After:
```bash
if [ -f .env ]; then
    if grep -q '^DB_DATABASE=' .env; then
        sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_FILE|" .env
    else
        printf '\nDB_DATABASE=%s\n' "$DB_FILE" >> .env
    fi
fi
```

Two changes:
1. Removed the `if [ -n "${DB_DATABASE:-}" ]` guard — materialization
   now always fires.
2. Changed `$DB_DATABASE` to `$DB_FILE` in the sed/printf — so the
   materialized value is the resolved path (`/tmp/storefront/database.sqlite`
   by default), not the (possibly-unset) env var.

See ADR-008 for the full reasoning, alternatives considered, and the
consequences future agents must preserve.

## Verification
- `sh -n docker/entrypoint.vercel.sh`: PASS (shell syntax check).
- Static review of the changed block: PASS — materialization now fires
  unconditionally and uses `$DB_FILE` (the resolved path) instead of
  `$DB_DATABASE` (the env var, possibly unset).
- Static review confirms the Render entrypoint (`docker/entrypoint.sh`)
  is unchanged: PASS.
- Static review confirms `config/database.php` is unchanged: PASS.
- Vercel live verification: PENDING (no Vercel CLI or Docker daemon in
  this environment; user must trigger a fresh Vercel build).

## Commits

### Commit — e422ae6
Date: 2026-09-02
Branch: main

Summary:
fix(vercel): always materialize DB_DATABASE into .env to fix request-time
DB crash

The Vercel deployment crashed on every request with DatabaseManager.php
line 226 (500 INTERNAL_SERVER_ERROR) because Laravel fell back to its
default SQLite path (/var/www/html/database/database.sqlite) at request
time — that path is in the read-only image layer on Vercel.

Root cause: the entrypoint's DB_DATABASE materialization was gated on
`if [ -n "${DB_DATABASE:-}" ]`, which only fired when the env var was
explicitly set. On Vercel no such env var is set, so .env kept its
commented-out `# DB_DATABASE=laravel` line and Laravel used the default
path.

Fix: remove the guard and always materialize DB_DATABASE=$DB_FILE into
.env, where $DB_FILE is the /tmp/storefront/database.sqlite path the
entrypoint just created and migrated. Both migrate-time (CLI) and
request-time (Apache mod_php) now read the same path.

The Render entrypoint is unchanged — its conditional materialization
works on Render because /var/www/html/database/ is writable there at
request time. The two entrypoints now intentionally diverge on this
point (ADR-008).

Changes:
- docker/entrypoint.vercel.sh: remove `if [ -n "${DB_DATABASE:-}" ]`
  guard around DB_DATABASE materialization; use $DB_FILE instead of
  $DB_DATABASE in the sed/printf.
- .agent/OBSERVATIONS/OBS-009-vercel-db-path-mismatch.md (new)
- .agent/DECISIONS/ADR-008-vercel-db-path-materialization.md (new)
- .agent/TASKS/TASK-006-vercel-db-path-fix.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-007.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-007.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-007.md (new)

Verification:
- sh -n docker/entrypoint.vercel.sh: PASS
- Static review of changed block: PASS
- Render entrypoint unchanged: PASS
- config/database.php unchanged: PASS
- Vercel live verification: PENDING user redeploy

Notes:
- Did NOT modify docker/entrypoint.sh (Render entrypoint) — its
  conditional materialization is correct for Render's filesystem
  semantics.
- Did NOT modify config/database.php — application config should not
  carry deployment-target-specific paths.
- Did NOT add DB_DATABASE as a Vercel dashboard env var recommendation —
  the entrypoint's .env materialization is the deterministic fix and
  doesn't require user setup.

## Remaining Work
- User: trigger a fresh Vercel build (push to main or hit Redeploy in
  the Vercel dashboard). The DB crash should be resolved.
- User: visit the deployed URL and confirm:
  1. The landing page (/) renders with the "Event Ledger" styling.
  2. The contact form (/contact) submits successfully and shows the
     thank-you page.
  3. Hard-refresh (Cmd/Ctrl+Shift+R) to bypass any cached 500 response.
- If the Vercel deployment still crashes after this fix, the next most
  likely cause is the storage/ symlink failing (rm -rf storage fails
  because /var/www/html is read-only even for the entrypoint user).
  Capture the Vercel build log + runtime log and look for "permission
  denied" or "read-only file system" errors during the entrypoint's
  step 1.
