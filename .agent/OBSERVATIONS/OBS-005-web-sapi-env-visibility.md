# OBS-005

## Date
2026-09-02

## Observation
Container environment variables do not reliably reach Laravel's `env()`
inside the PHP web worker:

1. `php artisan serve` filters the child `php -S` environment whenever a
   `.env` file exists — `ServeCommand::startProcess()` passes only
   allow-listed variables and sets the rest to `false`. Any env override
   silently disappears under the dev server.
2. phpdotenv's default adapters (EnvConst/ServerConst) cannot see real
   environment variables when the SAPI does not populate `$_ENV`/`$_SERVER`
   — e.g. `php.ini-production` sets `variables_order=GPCS` (no E), which is
   the likely state under Apache mod_php. `getenv()` is not consulted by the
   default repository.

CLI processes are fine: real environment variables are visible AND win over
`.env` values there (verified with `APP_ENV` and `APP_DEBUG`).

## Evidence
- Docker runtime simulation: valid contact POST returned 500 — the web
  worker resolved `DB_DATABASE` to null (fell back to the default path)
  while the entrypoint's CLI migrate had used the override. Exception:
  "Database file at path [.../database/database.sqlite] does not exist."
- `vendor/laravel/framework/src/Illuminate/Foundation/Console/ServeCommand.php`
  — the env-filtering closure in `startProcess()`.
- `Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()->immutable()`
  returns NULL for a set env var when `$_ENV`/`$_SERVER` are emptied
  (variables_order=GPCS test).
- `APP_ENV=local php artisan env` -> `local` (real env wins over
  `.env`'s `production` in CLI); same for `APP_DEBUG`.

## Impact
Any Docker/Laravel wiring that assumes `docker run -e KEY=value` is visible
to web requests is fragile. The storefront's entrypoint materializes
`DB_DATABASE` into `.env` at container start instead (deterministic in every
SAPI), which is what docker-compose relies on for the persistent volume. A
future agent adding env-var-driven configuration for the web layer must
either materialize it into `.env` at start or mount a config override —
never rely on the env var reaching mod_php.

## Related Task
TASK-002-docker-deployment

## Status
CONFIRMED
