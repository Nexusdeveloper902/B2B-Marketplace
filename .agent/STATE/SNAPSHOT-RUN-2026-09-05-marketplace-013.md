# STATE SNAPSHOT — RUN-2026-09-05-marketplace-013

## Overall Status
HEALTHY locally, PUSH BLOCKED. The storefront is now stateless (no
database), hardened (no /__debug, .env untracked, throttled contact form),
and fully tested. main @ aa94ca2 verified (17 tests / 120 assertions).
The remote push failed with 403 (token lacks Contents:write) — remote
origin/main is still at ecde2d5 until a write-scoped push happens.

## Completed
- TASK-011-stateless-storefront (this run):
  - Database layer removed entirely (model, migration, database/, DB env)
  - Contact form validates + logs (Log::info('contact.request')); no DB
  - /__debug endpoint removed (regression-tested)
  - .env untracked + gitignored (real APP_KEY no longer shipped; history
    keeps it — treated as public, no deployment uses it)
  - Contact form throttled (5/min)
  - StatelessArchitectureTest guards the contract (17 tests total)
  - ADR-013 supersedes ADR-001; README + PROJECT.md document statelessness
  - Fresh-clone DX fixed (test-only APP_KEY; quickstart key step)
- All prior tasks TASK-001..TASK-010 remain completed (unchanged).

## In Progress
- Nothing.

## Blocked
- Remote push of main + feature/TASK-011-stateless-storefront (403 on the
  provided token — needs Contents:write or a manual push).

## Known Problems
- The historical APP_KEY from the old committed .env remains in git
  history (public). It is no longer used by any deployment (keys are
  generated per environment at startup). History purge would require an
  authorized rewrite (out of scope, §7.12).
- Contact leads exist only in deployment logs (by design, ADR-013).
- Container-image builds were not exercisable in this sandbox (no Docker
  daemon, OBS-004); both deployment paths were verified statically +
  via shell syntax checks + artisan/route/test smoke runs. Vercel
  entrypoint changes are removals plus a no-write env fallback built
  from previously proven primitives.

## Important Current Facts
- The app has NO database, NO migrations, NO models. Do not reintroduce
  without a new ADR superseding ADR-013.
- Contact submissions -> application log only (stderr on Vercel,
  storage/logs/laravel.log on Render/local).
- .env is NOT in the repository. Entrypoints materialize it:
  - Render (entrypoint.sh): cp .env.example .env + key:generate --force
  - Vercel (entrypoint.frankenphp.sh): sources .env or .env.example
    directly (no writes — read-only FS), generates APP_KEY at start if
    absent. Set APP_KEY in Vercel project settings for key stability.
- phpunit.xml carries a disposable test-only APP_KEY; `php artisan test`
  works on a fresh clone with zero setup.
- Quickstart: composer install; cp .env.example .env; php artisan
  key:generate --force; php artisan serve.
- Toolchain: static PHP 8.4.8 + Composer 2.10.3 at /home/z/my-project/tools/
  (re-provision per OBS-002/OBS-017 when the sandbox resets).

## Current Main Commit
aa94ca2 (merge: RUN-013 follow-up — fresh-clone fix)

## Current Main Status
BUILDABLE — verified: 17 tests / 120 assertions PASS; live serve smoke
test: 5 pages 200, /__debug 404, CSRF active, submissions logged.

## Active Branches
- main @ aa94ca2 (local; remote still ecde2d5)
- feature/TASK-011-stateless-storefront (fully merged; kept for reference)
