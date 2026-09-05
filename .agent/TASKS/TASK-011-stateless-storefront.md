# TASK-011-stateless-storefront

## Objective
Product decision (owner, 2026-09-05): the marketplace storefront does not
need a database. Remove the debug endpoint and the entire DB layer,
explicitly document that no DB is needed, apply the remaining security
fixes from the 2026-09-05 code-quality review (tracked `.env` with real
APP_KEY, unthrottled contact form), and add tests. Vercel deployment must
keep working.

This task supersedes the persistence requirement of TASK-001 (SQLite,
ADR-001) and supersedes ADR-001 via ADR-013. It also resolves the
`/__debug` known problem recorded in SNAPSHOT-RUN-2026-09-02-marketplace-012.

## Constraints
- Do not break the Vercel deployment (confirmed working, OBS-016) or the
  Render/docker-compose path.
- No history rewriting (protocol §7.12): `.env` is untracked going forward;
  git history keeps the old file — the exposed APP_KEY is considered public
  and is no longer used by any deployment (rotation not applicable since
  keys are now generated per-environment at startup).

## Commits

## Commit — 8bced93

Date: 2026-09-05
Branch: feature/TASK-011-stateless-storefront

Summary:
Removed the database layer entirely; contact form logs submissions.

Changes:
- Deleted app/Models/ContactRequest.php, database/ (migration, seeder, .gitignore)
- ContactController::store(): validate -> Log::info('contact.request', $validated) -> redirect
- Removed DB_* vars from .env / .env.example / phpunit.xml
- docker-compose.yml: removed storefront-data volume + DB env
- entrypoint.sh: removed SQLite materialization + migrate; chown list trimmed
- entrypoint.frankenphp.sh: removed SQLite/migrate; env loading falls back to
  sourcing .env.example directly (no writes — read-only FS safe, OBS-010);
  APP_KEY generated at startup when missing (key:generate --show, no FS write)
- Dockerfile / Dockerfile.vercel: comments + extension trim (pdo_sqlite/sqlite3 out)
- README "Why there is no database"; PROJECT.md Data section updated
- ADR-013 created (supersedes ADR-001); lang en/es privacy copy updated
- ContactFormTest rewritten: log-based submission assertions

Verification:
- php artisan test: 14 passed (103 assertions)
- sh -n on both entrypoints: PASS
- php artisan route:list: clean, no DB-dependent routes

Notes:
- config/database.php intentionally left untouched (inert framework file;
  minimal-diff decision recorded in ADR-013).
- Static PHP toolchain re-provisioned in the sandbox (tools dir from
  OBS-002 was lost to the ephemeral filesystem) — see OBS-017.

## Commit — 66971a7

Date: 2026-09-05
Branch: feature/TASK-011-stateless-storefront

Summary:
Security hardening: removed /__debug, untracked .env, throttled contact form.

Changes:
- routes/web.php: /__debug route removed (was flagged in SNAPSHOT-012)
- POST /contact -> middleware('throttle:5,1')
- git rm --cached .env; /.env added to .gitignore
- routes/web.php carries a comment block explaining the removal (ADR-013)

Verification:
- php artisan test: 14 passed (103 assertions)
- route:list: no debug route
- git ls-files: no tracked .env

## Commit — 5ce315a

Date: 2026-09-05
Branch: feature/TASK-011-stateless-storefront

Summary:
StatelessArchitectureTest — regression guards for the stateless contract.

Changes:
- tests/Feature/StatelessArchitectureTest.php:
  - /__debug returns 404 (also with ?token=debug); Route::has('debug') false
  - ContactRequest class absent, database/migrations and database/seeders absent
  - contact form throttle: 5 submissions pass, 6th -> 429

Verification:
- php artisan test: 17 passed (120 assertions)
- Note: a stale sandbox classmap (vendor/ not refreshed after file deletion)
  caused a false positive until `composer dump-autoload`; not reproducible
  from a fresh clone.
