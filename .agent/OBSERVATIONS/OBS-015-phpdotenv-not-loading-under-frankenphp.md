# OBS-015

## Date
2026-09-02

## Observation
**phpdotenv does not load `/app/.env` under FrankenPHP's persistent
process model.** This is the root cause of multiple cascading failures:

1. `config('app.key')` returns null → `MissingAppKeyException` when
   the `Encrypter` is resolved (needed by `SESSION_DRIVER=cookie`).
2. `config('app.locale')` returns null → Laravel can't determine the
   locale → translator returns translation keys as strings instead of
   arrays → `foreach()` crash in Blade views.
3. `config('app.supported_locales')` returns null → `SetLocale` middleware
   can't resolve a supported locale.

## Evidence
- User-reported Vercel error (2026-09-02, deployment
  `dpl_Fy3ADcferFSYoELXEovKNh3KfWBr`):
  ```
  MissingAppKeyException: No application encryption key has been specified.
  at EncryptionServiceProvider.php:83
  ```
  The shipped `.env` has `APP_KEY=base64:PhyT2eU24UtKUnJ4LgYfTCbq7Jytyt/7qHopDLnUNjQ=`
  — the key IS in the file, but phpdotenv isn't loading it.
- After fixing APP_KEY, the next error was:
  ```
  ErrorException: foreach() argument must be of type array|object, string given
  at landing.blade.php:41
  ```
  Line 41: `@foreach (__('landing.ledger.columns') as $column)` — the
  translator returned the key string `"landing.ledger.columns"` instead
  of the array `['time', 'card', 'reader', 'event']` because
  `config('app.locale')` was null (phpdotenv didn't load
  `APP_LOCALE=en` from `.env`).
- The fix: `set -a; . ./.env; set +a` in the entrypoint loads ALL env
  vars from `.env` into the shell environment. FrankenPHP reads shell
  env vars natively and passes them to PHP, so `env('APP_KEY')`,
  `env('APP_LOCALE')`, etc. all resolve correctly without depending on
  phpdotenv.

## Why phpdotenv fails under FrankenPHP
FrankenPHP uses a persistent PHP process (unlike traditional PHP-FPM
which spawns fresh workers per request). phpdotenv's `Loader` loads
`.env` once at boot, but under FrankenPHP's persistent model, the
bootstrapping happens in a context where the `.env` file path may not
resolve correctly (working directory, base path, or file permissions
may differ). The exact mechanism is not fully diagnosed — the fix
bypasses phpdotenv entirely by loading env vars at the shell level.

## Impact
- Any Laravel-on-Vercel deployment using FrankenPHP will have broken
  config resolution if it relies on phpdotenv loading `.env`.
- The fix: load ALL env vars from `.env` into the shell environment
  using `set -a; . ./.env; set +a` BEFORE the Vercel-specific overrides.
  FrankenPHP passes shell env vars to PHP natively.
- The Render deployment is unaffected — Apache+mod_php loads `.env`
  correctly via phpdotenv on Render's filesystem.

## Related Task
TASK-010-vercel-storage-env-overrides

## Status
CONFIRMED — by two sequential user-reported Vercel errors
(MissingAppKeyException, then foreach() on string) that were both
caused by phpdotenv not loading `.env`. Fix applied in commits
`2b6b0b7` (APP_KEY export) and `e896c84` (full .env loading via set -a)
in RUN-011.
