# OBS-012

## Date
2026-09-02

## Observation
The official `php:apache` Docker image does NOT pass OS environment
variables to PHP by default. Apache's `mod_env` module requires explicit
`SetEnv` or `PassEnv` directives in the vhost configuration to forward
environment variables to PHP's `$_ENV` / `$_SERVER` / `getenv()`.

This is the root cause of why all Apache+mod_php-based Vercel fixes
(RUN-005 through RUN-009) failed to resolve the `DatabaseManager.php
line 226` crash:

1. The entrypoint (`docker/entrypoint.vercel.sh`) sets `DB_DATABASE`
   as a shell environment variable.
2. Apache starts and spawns worker processes as `www-data`.
3. **Apache does NOT pass `DB_DATABASE` to PHP** (no `PassEnv DB_DATABASE`
   directive in the vhost).
4. Laravel's `env('DB_DATABASE')` returns null.
5. Laravel falls back to `database_path('database.sqlite')` =
   `/var/www/html/database/database.sqlite`.
6. On Vercel's read-only image layer at request time, SQLite can't
   open the file for writing → `DatabaseManager.php line 226` crash.

The `.env` materialization approach (ADR-008) was supposed to work
around this by writing `DB_DATABASE` directly to `.env`. But:
- `sed -i .env` may fail on Vercel's read-only filesystem
- Even if it succeeds, `is_writable()` returns true for root even on
  read-only filesystems, so the config-level fallback (ADR-009) doesn't
  trigger

## Evidence
- Stack Overflow (search result): "If you are using php official Docker
  image you have to explicitly pass environment variables from Apache to
  PHP. you'll also have to use SetEnv directive"
  (https://stackoverflow.com/questions/35953019/expose-environment-variables-to-apache-and-php)
- docker-library/php GitHub issue #74: "Environment variables ignored
  by php-fpm" (same underlying issue affects mod_php)
- Doppler blog: "Apache uses the mod_env module for defining environment
  variables using the SetEnv directive"
- Vercel's official PHP guide uses FrankenPHP (not Apache+mod_php),
  which reads env vars natively
- The Apache vhost in this repo (`docker/apache/vhost.vercel.conf`) has
  NO `SetEnv` or `PassEnv` directives — confirming the env vars were
  never passed to PHP.

## Impact
- Any Vercel deployment using Apache+mod_php will fail to pass env vars
  to PHP unless `SetEnv`/`PassEnv` directives are added to the vhost.
- This affected ALL previous Vercel fix attempts. The fix is to switch
  to FrankenPHP (ADR-011), which reads env vars natively.
- The Render deployment (Apache+mod_php via `docker/entrypoint.sh`) is
  unaffected because Render's filesystem is writable and the entrypoint's
  `.env` materialization works there (Render doesn't have the read-only
  image layer issue at request time).

## Related Task
TASK-009-switch-to-frankenphp

## Status
CONFIRMED — by Vercel's official documentation (which uses FrankenPHP,
not Apache), Stack Overflow evidence, and the docker-library/php issue
tracker. Fix applied in RUN-2026-09-02-marketplace-010 via ADR-011.
