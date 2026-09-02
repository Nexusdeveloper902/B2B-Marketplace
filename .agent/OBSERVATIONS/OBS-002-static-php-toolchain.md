# OBS-002: Execution environment has no system PHP/Composer — static toolchain used

## Date
2026-09-02

## Observation
This machine (Debian 13, user `z`, no root/sudo) has no system `php`, `composer`,
or `npm`. PHP was provisioned as a static CLI binary and Composer as a phar:

- PHP 8.4.23 static build (from dl.static-php.dev, "common" bundle):
  `/home/z/my-project/tools/php`
- Composer 2.10.3 phar: `/home/z/my-project/tools/composer.phar`
- Invoke as: `/home/z/my-project/tools/php /home/z/my-project/tools/composer.phar ...`
  and `/home/z/my-project/tools/php artisan ...`
- Bundled extensions cover all Laravel requirements (pdo_sqlite, sqlite3,
  mbstring, openssl, tokenizer, dom, curl, session, fileinfo, bcmath, zip, phar).

## Evidence
- `php: command not found`, `composer: command not found` at run start.
- `./php -v` → PHP 8.4.23 (cli); `composer.phar --version` → Composer 2.10.3.
- Full `composer create-project laravel/laravel` + migrate + serve succeeded with
  these binaries.

## Impact
- Future agent runs in this same environment must use the same tool paths (or
  re-provision). `php artisan test` works via the static binary.
- No `npm`/Node tooling is available — consistent with the task's "no Node/Vite
  build pipeline" constraint (plain CSS + self-hosted fonts chosen accordingly).
- Laravel 13 requires PHP >= 8.3; the static 8.4 build satisfies it.

## Related Task
TASK-001-marketplace-mvp

## Status
CONFIRMED
