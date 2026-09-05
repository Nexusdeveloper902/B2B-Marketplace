# ADR-013: Stateless storefront — no database layer at all

## Date
2026-09-05

## Context
The storefront's only dynamic feature is the contact/demo form, which until
TASK-011 persisted submissions to a SQLite `contact_requests` table
(ADR-001). Three facts forced a rethink:

1. The product owner explicitly decided the marketplace does not need a
   database ("remove any db layer") — the marketing site has no operator
   process that reads `contact_requests`, so the table was write-only data.
2. The deployment reality already treated submissions as ephemeral: on
   Vercel the SQLite file lives in an ephemeral `/tmp` (OBS-007), so
   submissions were lost on every cold restart anyway.
3. The database brought hard deployment constraints with it — the
   DB-path materialization dance (ADR-008, ADR-009, OBS-009), read-only
   filesystem hazards, and a `migrate` step in every container start —
   for a single write-only table.

At the same time, TASK-011 also fixes two security findings from a code
quality review: the unauthenticated `/__debug` diagnostic endpoint
(flagged "remove before production" in SNAPSHOT-012) and the tracked
`.env` file containing a real production `APP_KEY` in a public repository.

## Decision
1. **Remove the database layer entirely**: the `ContactRequest` model, the
   `contact_requests` migration, the whole `database/` directory, and every
   DB-related env var (`DB_CONNECTION`, `DB_DATABASE`, …) from `.env`,
   `.env.example`, `phpunit.xml`, and `docker-compose.yml` (including the
   `storefront-data` volume).
2. **Contact submissions are validated server-side and written to the
   application log** (`Log::info('contact.request', …)`): stderr on Vercel
   (captured by the Vercel log drain), `storage/logs/laravel.log` on
   Render / local. This is the only record of a submission — deliberate.
3. **Untrack `.env`** (add to `.gitignore`, `git rm --cached`). Entrypoints
   materialize runtime config instead:
   - Render (`entrypoint.sh`): `cp .env.example .env` + `key:generate
     --force` when no APP_KEY env var is set (filesystem is writable).
   - Vercel (`entrypoint.frankenphp.sh`): source `.env` when present,
     otherwise source `.env.example` **directly** (sourcing performs no
     writes — a `cp` would fail under `set -e` on the read-only FS,
     cf. OBS-010), then `php artisan key:generate --show` exported as
     `APP_KEY` when none was loaded. Consequence: fresh key per cold
     start; cookie sessions reset across deploys — acceptable for a site
     with no authenticated state. Setting `APP_KEY` in Vercel project
     settings restores key stability.
4. **Remove the `/__debug` endpoint** from `routes/web.php` (regression
   test added).
5. **Throttle the contact form** with Laravel's `throttle:5,1` middleware.
6. `config/database.php` stays untouched as an inert framework file —
   nothing in the app resolves a connection. Trimming framework files was
   rejected to keep the Laravel skeleton standard and the diff minimal.

## Alternatives Considered
- Keep SQLite on Render only — rejected: two divergent behaviors per
  platform, and the write-only table still has no consumer.
- Replace the DB with a file-based store (JSON lines) — rejected: still a
  persistence surface on ephemeral/readonly FS; log drain is the natural
  channel on both platforms.
- Keep committed `.env` but strip APP_KEY — rejected: a tracked `.env`
  invites real secrets back in; both entrypoints already had fallback
  paths that only needed extending.

## Reasoning
The product is a static-feeling marketing site. Statelessness removes the
last deployment hazard that made the Vercel bring-up take 11 runs
(ADB-006..ADR-012, OBS-009..OBS-015): there is no DB file to materialize,
no migrate to run, nothing to persist. `composer install && php artisan
serve` is now the entire runtime contract.

## Consequences
- Contact leads exist only in deployment logs; if real lead capture is
  ever required, it needs a new explicit decision (hosted form backend or
  managed DB).
- The APP_KEY rotation-per-cold-start on Vercel invalidates cookie
  sessions on redeploy — invisible to a site with no login state.
- The old production APP_KEY that was committed in `.env` must still be
  considered public (it remains in git history — history rewriting is out
  of scope per the run protocol); it is no longer used by any deployment.
- `php artisan migrate` is no longer part of setup; docs and CI instructions
  updated accordingly.

## Status
ACTIVE

## Supersedes
ADR-001-sqlite-over-server-database.md (the SQLite persistence decision —
its persistence requirement is revoked; its "zero external services"
spirit is preserved and extended)
