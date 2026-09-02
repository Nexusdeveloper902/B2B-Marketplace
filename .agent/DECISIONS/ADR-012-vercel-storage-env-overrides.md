# ADR-012

## Date
2026-09-02

## Context
After switching to FrankenPHP (ADR-011), the Vercel deployment still
crashed on every request. Deep debugging across 4 commits revealed
three compounding issues, all stemming from Vercel's read-only container
filesystem and phpdotenv not loading `.env`:

1. **OBS-013**: `/app` is read-only at request time. The `storage/`
   symlink approach fails silently. Laravel crashes because it can't
   write compiled views, sessions, cache, or logs.

2. **OBS-014**: The `PreventRequestsDuringMaintenance` middleware runs
   on every request. The default `file` maintenance driver writes to
   `storage/framework/` (read-only) → `MaintenanceModeManager::getDefaultDriver()`
   returns null → `createDriver()` called with 0 args →
   `ArgumentCountError` at bootstrap.

3. **OBS-015**: phpdotenv doesn't load `/app/.env` under FrankenPHP's
   persistent process model. `config('app.key')` returns null →
   `MissingAppKeyException`. Then `config('app.locale')` returns null
   → translator returns keys as strings → `foreach()` crash in Blade.

## Decision
Override ALL Laravel storage paths and config drivers via environment
variables, which FrankenPHP reads natively. Load ALL env vars from
`.env` into the shell environment using `set -a; . ./.env; set +a`
BEFORE the Vercel-specific overrides.

The entrypoint (`docker/entrypoint.frankenphp.sh`) now:

1. **Step 1b**: `set -a; . ./.env; set +a` — loads every KEY=VALUE
   from `.env` into the shell env (APP_KEY, APP_LOCALE, APP_NAME,
   APP_SUPPORTED_LOCALES, etc.)

2. **Step 2b**: Unconditionally overrides file-I/O drivers:
   - `VIEW_COMPILED_PATH=/tmp/storefront/framework/views`
   - `SESSION_DRIVER=cookie` (no file I/O, no DB I/O)
   - `CACHE_STORE=array` (in-memory per request)
   - `APP_MAINTENANCE_DRIVER=cache` + `APP_MAINTENANCE_STORE=array`
   - `LOG_CHANNEL=stderr` + `LOG_STACK=stderr`
   - `APP_DEBUG=true` (temporary, for debugging)
   - `DB_DATABASE=/tmp/storefront/database.sqlite`

   These are **unconditional** (not `${VAR:-default}`) because the
   `.env` values (`SESSION_DRIVER=file`, etc.) would override them
   otherwise, and those file-based drivers crash on Vercel's read-only FS.

3. **Added `config/view.php`** to explicitly support the
   `VIEW_COMPILED_PATH` env var (Laravel's built-in default doesn't
   include it in the config file).

## Alternatives Considered
- **Fix phpdotenv to load under FrankenPHP.** Rejected: phpdotenv's
  loading mechanism under persistent processes is complex and fragile.
  Bypassing it with shell-level env loading is simpler and more
  reliable.

- **Use `php artisan config:cache` to bake config into a cached file.**
  Rejected: the cached config would still reference file-based paths
  unless we also override env vars. And config caching makes iteration
  harder.

- **Switch to `cookie` session driver only.** Insufficient: the
  maintenance mode crash (OBS-014) and the view compilation crash
  (OBS-013) also need env var overrides.

## Reasoning
FrankenPHP reads OS env vars natively and passes them to PHP. By
loading `.env` at the shell level and overriding file-I/O drivers, we
eliminate ALL filesystem dependencies on the read-only `/app` layer.
Every storage path either:
- Points to `/tmp` (writable, ephemeral), OR
- Uses an in-memory driver (cookie, array), OR
- Goes to stderr (captured by Vercel logs)

This is the only reliable approach for Vercel's read-only container
filesystem.

## Consequences
- The Vercel deployment should now serve requests successfully (pending
  user-side verification — the last known error was the `foreach()`
  crash which this fix addresses).
- `config/view.php` is a new file that explicitly supports
  `VIEW_COMPILED_PATH`.
- The Render deployment is unaffected — its entrypoint (`docker/entrypoint.sh`)
  and config defaults remain unchanged.
- The entrypoint's `set -x` trace mode produces verbose Vercel logs.
  Can be removed once deployment is confirmed working.
- `APP_DEBUG=true` is set temporarily — should be changed to `false`
  once the deployment is confirmed working.
- Future agents must NOT remove the env var overrides or the `.env`
  loading step. They are required for Vercel.

## Status
ACTIVE

## Supersedes
none (ADR-011's FrankenPHP switch is still correct; this ADR documents
the env var overrides that make FrankenPHP work on Vercel's read-only FS)
