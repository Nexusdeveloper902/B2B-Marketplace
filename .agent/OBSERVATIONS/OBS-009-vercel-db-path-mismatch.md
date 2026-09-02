# OBS-009

## Date
2026-09-02

## Observation
After RUN-2026-09-02-marketplace-006 fixed the `@vercel/docker` builder
deprecation, the Vercel deployment still crashed on every request with:

```
500: INTERNAL_SERVER_ERROR
Code: FUNCTION_INVOCATION_FAILED

In DatabaseManager.php line 226:
Application exited with code 1.
```

Root cause: a path mismatch between where the entrypoint creates the
SQLite database file and where Laravel looks for it at request time.

### The mismatch

The Vercel entrypoint (`docker/entrypoint.vercel.sh`) does this:

```bash
DB_FILE="${DB_DATABASE:-$EPHEMERAL_ROOT/database.sqlite}"   # /tmp/storefront/database.sqlite
mkdir -p "$(dirname "$DB_FILE")"
[ -f "$DB_FILE" ] || touch "$DB_FILE"
# ...
if [ -n "${DB_DATABASE:-}" ] && [ -f .env ]; then   # <-- BUG: only fires if env var is set
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
fi
php artisan migrate --force
```

On Vercel, `DB_DATABASE` is NOT set as a container env var (the user
hasn't configured one on the Vercel dashboard). So:

1. `DB_FILE` resolves to `/tmp/storefront/database.sqlite` (correct —
   that's where we want it).
2. The entrypoint creates + touches that file.
3. The `if [ -n "${DB_DATABASE:-}" ]` materialization block is SKIPPED
   because the env var is empty.
4. `php artisan migrate --force` runs. The CLI sees the real env var
   (`DB_DATABASE` is unset), so Laravel's `config/database.php` falls
   back to `database_path('database.sqlite')` = `/var/www/html/database/database.sqlite`.
5. The migrate succeeds — `/var/www/html/database/` IS writable during
   the entrypoint's lifetime (root user, image layer is writable at
   container start), so SQLite PDO creates the file there.
6. The entrypoint `exec`s Apache. Apache workers run as `www-data`.
7. At request time, Laravel reads `.env` again. `DB_DATABASE` is still
   not materialized in `.env` (the entrypoint skipped that step).
8. Laravel falls back to `database_path('database.sqlite')` =
   `/var/www/html/database/database.sqlite`.
9. **The file exists** (migrate created it in step 5) **but the
   directory is now read-only** for `www-data` — Vercel's container
   runtime may switch the image layer to read-only after the
   entrypoint hands off to Apache. Or `www-data` doesn't have write
   permission on the file (because the entrypoint's `chown` only fires
   when running as root, and Vercel may not).
10. SQLite PDO fails with "unable to open database file" (write-mode
    open fails because the directory or file isn't writable).
11. Laravel surfaces this as a `DatabaseManager` exception at line 226
    (the `makeConnection` / `connect` path).
12. The container eventually exits with code 1 (Laravel's exception
    handler may also crash if it can't write to storage/logs).

### The fix

Always materialize `DB_DATABASE=$DB_FILE` into `.env`, not just when
the env var is set. This forces Laravel to look at `/tmp/storefront/database.sqlite`
(the file the entrypoint created and migrated), which is in `/tmp`
(world-writable by definition, survives for the container lifetime,
writable by `www-data` without any `chown` needed).

This differs from the Render entrypoint (`docker/entrypoint.sh`), which
only materializes `DB_DATABASE` when the env var is explicitly set. On
Render, the default Laravel path (`/var/www/html/database/database.sqlite`)
works because Render's container filesystem is writable at request time
and the `chown` step makes it accessible to `www-data`. On Vercel,
neither assumption holds.

## Evidence
- User-reported Vercel logs on 2026-09-02:
  ```
  {"level":"info","message":"In DatabaseManager.php line 226:",
   "requestMethod":"GET","requestPath":"/","responseStatusCode":500,...}
  {"level":"error","message":"Application exited with code 1.",
   "requestMethod":"GET","requestPath":"/","responseStatusCode":500,...}
  ```
- Repository inspection of `docker/entrypoint.vercel.sh` lines 86-92
  (pre-fix): the `if [ -n "${DB_DATABASE:-}" ]` guard caused the
  materialization to be skipped when no env var was set.
- `config/database.php` line 38: `'database' => env('DB_DATABASE',
  database_path('database.sqlite'))` — confirms Laravel's fallback
  path is `/var/www/html/database/database.sqlite` when `DB_DATABASE`
  is unset.
- Vercel's official docs on Container Deployments: "the filesystem is
  ephemeral" — combined with OBS-007, this means both the file AND
  its parent directory may be read-only or unreachable at request
  time.
- No PHP/Docker runtime in this execution environment to reproduce
  empirically; the diagnosis is based on (a) the Vercel error log,
  (b) the entrypoint source code, and (c) Laravel's documented
  SQLite fallback behavior in `config/database.php`.

## Impact
- The Vercel deployment crashed on every request with 500
  INTERNAL_SERVER_ERROR until this fix.
- The Render deployment is unaffected — its entrypoint only
  materializes `DB_DATABASE` when the env var is set, which works on
  Render because the default path is writable there.
- Future agents must NOT remove the unconditional `DB_DATABASE`
  materialization in `entrypoint.vercel.sh` without confirming
  Vercel's container filesystem behavior has changed to make
  `/var/www/html/database/` writable at request time.

## Related Task
TASK-006-vercel-db-path-fix

## Status
CONFIRMED — by user-reported Vercel log evidence plus entrypoint source
inspection. Fix applied in RUN-2026-09-02-marketplace-007; Vercel-side
verification pending user redeploy.
