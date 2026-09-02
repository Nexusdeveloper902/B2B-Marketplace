# TASK-007-vercel-config-level-fallbacks

## Objective
Fix the persistent Vercel runtime crash (`DatabaseManager.php line 226`)
that survived RUN-007's `.env` materialization fix. Move the
deployment-target-specific fallbacks from the entrypoint (runtime `.env`
modification) to the config files (code-level, executed at config-load
time, no `.env` dependency).

## Origin
User-reported Vercel logs on 2026-09-02 (after RUN-007's fix was pushed):
same `DatabaseManager.php line 226` error on every request, new
deployment ID `dpl_5szrpPS4WyTZTjqdYs5xEy7to2zh`. The `.env`
materialization had no effect — likely because Vercel's filesystem is
read-only for `sed -i .env`. See OBS-010.

## Root Cause
Two compounding issues (OBS-010):

1. **Vercel's filesystem may be read-only for the entrypoint's `sed -i .env`.**
   If `.env` can't be modified, Laravel reads the shipped values. The
   shipped `.env` has `SESSION_DRIVER=file` and `CACHE_STORE=file`, BUT
   the config-file defaults are `database` for both. If `.env` is
   corrupted/unreadable, Laravel falls back to `database` — and every
   request triggers a DB connection (via session middleware).

2. **Laravel's default config values use `database` for session and cache.**
   `config/session.php` line 21: `env('SESSION_DRIVER', 'database')`.
   `config/cache.php` line 18: `env('CACHE_STORE', 'database')`.
   These defaults are wrong for this app (which uses file-based
   sessions and cache). Changing them to `file` makes the app work
   even without `.env`.

## Implementation
1. `config/database.php`: compute SQLite default path before `return`,
   fall back to `/tmp/storefront/database.sqlite` when the default
   directory isn't writable.
2. `config/session.php`: default from `'database'` to `'file'`.
3. `config/cache.php`: default from `'database'` to `'file'`.
4. `routes/web.php`: add `/__debug` route (no DB access) for diagnostics.
5. `docker/entrypoint.vercel.sh`: add `set -x` + echo diagnostics, make
   storage symlink non-fatal, make migrate non-fatal.

## Acceptance Criteria
- [x] `config/database.php` has a code-level SQLite path fallback.
- [x] `config/session.php` defaults to `'file'`.
- [x] `config/cache.php` defaults to `'file'`.
- [x] `/__debug` route exists and doesn't access the DB.
- [x] `docker/entrypoint.vercel.sh` has diagnostics + non-fatal steps.
- [x] `sh -n docker/entrypoint.vercel.sh` passes.
- [x] No changes to Render-only files (`Dockerfile.render`, `docker/entrypoint.sh`,
      `docker/apache/vhost.conf`).
- [x] ADR-009 + OBS-010 + TASK-007 + RUN-008 + ledger + snapshot written.
- [x] Committed and pushed.

## Commits

### Commit — {{COMMIT_HASH}}
Date: 2026-09-02
Branch: main

Summary:
fix(vercel): config-level fallbacks for DB path, session, cache + /__debug route

The .env materialization fix (ADR-008) didn't resolve the Vercel crash
because Vercel's filesystem may be read-only for sed -i .env. Moved the
fallbacks to the config files (code-level, no .env dependency):

- config/database.php: SQLite path falls back to /tmp/storefront/database.sqlite
  when the default directory isn't writable.
- config/session.php: default from 'database' to 'file'.
- config/cache.php: default from 'database' to 'file'.
- routes/web.php: /__debug route outputs DB/storage/extension diagnostics (no DB access).
- docker/entrypoint.vercel.sh: set -x trace + echo diagnostics, non-fatal storage symlink + migrate.

See ADR-009, OBS-010.
