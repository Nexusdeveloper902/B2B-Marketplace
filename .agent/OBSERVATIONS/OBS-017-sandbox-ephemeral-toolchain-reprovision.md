# OBS-017: Sandbox is ephemeral — static PHP toolchain must be re-provisioned each run

## Date
2026-09-05

## Observation
The sandbox filesystem does not persist across agent sessions. The static
PHP/Composer toolchain documented in OBS-002
(`/home/z/my-project/tools/php`, `/home/z/my-project/tools/composer.phar`)
was gone at the start of this run; only the repositories remained (they are
re-cloneable from origin, and /home/z/my-project/repos had them). The
OBS-002 recipe still works; the binary is now PHP 8.4.8 (was 8.4.23) from
the same dl.static-php.dev "common" bundle — Composer 2.10.3 unchanged.

## Evidence
- `ls /home/z/my-project/tools/` → No such file or directory
- Re-downloaded php-8.4.8-cli-linux-x86_64.tar.gz + composer.phar →
  `php -v` → PHP 8.4.8 (cli); `composer.phar --version` → 2.10.3
- Full `composer install` + `php artisan test` succeeded with these binaries.

## Impact
- Future runs must re-check `/home/z/my-project/tools/` before relying on
  it and re-provision per OBS-002 when missing (2 downloads, ~1 minute).
- A stale `vendor/composer/autoload_classmap.php` (from toolchain-era
  installs that predate file deletions) escalates failed includes into
  ErrorException under Laravel's test harness; `composer dump-autoload`
  clears it. Fresh clones are unaffected.

## Related Task
TASK-011-stateless-storefront

## Status
CONFIRMED
