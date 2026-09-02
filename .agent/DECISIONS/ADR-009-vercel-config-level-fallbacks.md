# ADR-009

## Date
2026-09-02

## Context
RUN-007 (ADR-008) added unconditional `DB_DATABASE` materialization to
the Vercel entrypoint's `.env` file. The fix was correctly pushed and
Vercel rebuilt the image, but the runtime crash
(`DatabaseManager.php line 226`) persisted on every request — including
static pages and `/favicon.ico`.

Investigation (OBS-010) revealed two compounding issues:

1. **Vercel's container filesystem may be read-only for the entrypoint's
   `sed -i .env` modification.** If `sed -i` fails (or corrupts the
   file), the `.env` values shipped in the image are the ones Laravel
   reads at request time. The shipped `.env` has `SESSION_DRIVER=file`
   and `CACHE_STORE=file`, but if the entrypoint's modification breaks
   the file, Laravel falls back to the **config-file defaults** — and
   those defaults are `database` for both session and cache.

2. **Laravel's default config values use `database` as the session and
   cache driver.** With `SESSION_DRIVER=database`, every HTTP request
   triggers a DB connection (to read/write the session) — BEFORE the
   controller runs. This is why every route (including `/favicon.ico`)
   crashes at `DatabaseManager.php line 226`: the session middleware
   tries to connect to the DB before routing completes.

The `.env`-based approach (ADR-008) is fragile because it depends on
the `.env` file being writable AND readable at runtime. On Vercel's
read-only filesystem, this assumption doesn't hold.

## Decision
Move the deployment-target-specific fallbacks from the entrypoint
(runtime `.env` modification) to the **config files** (code-level,
executed at config-load time, no `.env` dependency):

1. **`config/database.php`**: compute the SQLite default path BEFORE
   the `return [...]` statement. Check if `database_path('database.sqlite')`'s
   directory is writable. If not (Vercel), fall back to
   `/tmp/storefront/database.sqlite` (which the entrypoint creates and
   migrates). Safe for local dev and Render (where the default path IS
   writable, so the fallback doesn't kick in).

2. **`config/session.php`**: change the default from `'database'` to
   `'file'`. This app doesn't use DB-backed sessions; file sessions are
   correct for all environments. The `.env` override
   (`SESSION_DRIVER=file`) is now redundant but harmless.

3. **`config/cache.php`**: change the default from `'database'` to
   `'file'`. Same reasoning — this app doesn't use DB-backed cache.

4. **`routes/web.php`**: add a `/__debug` route that outputs DB config,
   file status, storage writability, and PHP extensions as JSON. This
   route performs NO DB access, so it works even when the DB is broken.
   Used to diagnose deployment issues without shell access.

5. **`docker/entrypoint.vercel.sh`**: add `set -x` (trace mode) and
   targeted `echo` diagnostics to stderr (captured by Vercel's logs).
   Make the storage symlink non-fatal (try subdirectory symlinks as
   fallback). Make the migrate step non-fatal (log warning, continue).
   These changes don't fix the bug — they make the next failure
  diagnosable.

## Alternatives Considered
- **Stay with the `.env`-only approach (ADR-008) and debug why `sed -i`
  fails on Vercel.** Rejected: even if we fix `sed -i`, the config
  defaults (`database` for session/cache) are wrong for this app. The
  code-level fix is more robust and also fixes the session/cache
  driver issue.

- **Set `SESSION_DRIVER=file` and `CACHE_STORE=file` as Vercel dashboard
  env vars.** Rejected: per OBS-005, Vercel env vars may not reliably
  reach Apache mod_php. The config-level default is deterministic.

- **Run `php artisan config:cache` during the Docker build to bake
  the config values into a cached file.** Rejected: the cached config
  would still reference the default DB path (`/var/www/html/database/...`),
  which is read-only on Vercel. Would need to be combined with the
  config-level fallback anyway. Also, config caching makes iteration
  harder (every config change requires a rebuild).

- **Switch to `cookie` session driver (no file I/O needed).** Rejected:
  cookie sessions have a 4KB size limit; while this app's session data
  is small (just a locale string), the file driver is more flexible and
  already works on Render. Consistency wins.

## Reasoning
The `.env`-based approach is the Laravel-idiomatic way to override
config per-environment, but it depends on `.env` being writable at
container-start time AND readable at request time. On Vercel's
read-only filesystem, this assumption fails silently.

The config-level fallback executes at config-load time (every request,
since there's no config cache), checks the actual filesystem state, and
chooses the right path/driver. It's deterministic, doesn't depend on
`.env` being writable, and is safe for all environments (local dev,
Render, Vercel) because the fallback only kicks in when the default
path is unwritable.

## Consequences
- The Vercel deployment should now serve requests successfully (pending
  user-side redeploy verification).
- Local dev and Render are unaffected — the config defaults now match
  what `.env` was already setting (`file` for session/cache), and the
  SQLite path fallback only kicks in when the default directory is
  unwritable.
- The `/__debug` route is always available (no auth). Future agents can
  use it to diagnose deployment issues. It should be removed (or
  auth-gated) if this app ever goes to production with real users.
- The entrypoint's `set -x` trace mode will produce verbose Vercel
  build logs. Future agents can remove `set -x` once the deployment is
  confirmed working.
- ADR-008 (the `.env` materialization approach) is NOT superseded —
  it's still active as a belt-and-braces layer. The config-level
  fallback (this ADR) is the primary fix; the `.env` materialization
  is the secondary layer that helps when the config-level fallback
  can't determine the right path (e.g., if the default directory IS
  writable but the file isn't).

## Status
ACTIVE

## Supersedes
none (ADR-008 remains ACTIVE as a secondary layer; this ADR adds the
config-level fallback as the primary fix)
