# RUN RUN-2026-09-05-marketplace-013

## Task
TASK-011-stateless-storefront

## Agent Role
Full-Stack Engineer (Laravel / Blade) — stateless rework + security fixes.

## Result
COMPLETED locally (main integrated at aa94ca2, verified) / PUSH BLOCKED
(403 — token lacks Contents:write; see Problems/Blockers and ledger)

## Resume Notes
- The run brief referenced RUN-2026-09-02-marketplace-002 as "current" and
  an interrupted RUN-...-001. Repository reality: RUN-002..RUN-012 records
  exist, main was clean at `ecde2d5`, no work was lost. Per §1.1 repository
  reality wins; this run continues the sequence as 013.
- Prior-run documentation was fully recovered (.agent/ complete). Nothing
  needed reconstruction except the sandbox toolchain (see OBS-017): the
  static PHP/Composer tools documented in OBS-002 had been lost with the
  ephemeral sandbox; re-provisioned per the OBS-002 recipe.
- `git status` initially reported 172 modified files: pure filemode noise
  (100644→100755) from the 0777 sandbox filesystem, 0 content changes.
  Resolved locally with `git config core.filemode false` (not committed).

## Summary
Removed the database layer from the storefront entirely (model, migration,
database/ dir, DB env vars, compose volume, entrypoint SQLite/migrate
steps), replaced persistence with application logging, explicitly
documented the stateless product decision (ADR-013 supersedes ADR-001;
README + PROJECT.md), removed the unauthenticated /__debug endpoint,
untracked the committed .env (real APP_KEY in a public repo), throttled
the contact form, and added a 3-test architecture guard suite. Both
deployment paths (Render/Docker and Vercel/FrankenPHP) were kept working:
the Vercel entrypoint gained a read-only-FS-safe env fallback (source
.env.example directly, no cp) and startup APP_KEY generation, replacing
the removed migrate step.

## Changes Made
- See TASK-011 commit entries (8bced93, 66971a7, 5ce315a) — full detail there.

## Files Changed
- app/Http/Controllers/ContactController.php (rework)
- app/Models/ContactRequest.php (deleted)
- database/** (deleted)
- routes/web.php (debug route out, throttle in)
- docker/entrypoint.sh, docker/entrypoint.frankenphp.sh (stateless + env fallback + key gen)
- Dockerfile, Dockerfile.vercel, docker-compose.yml (stateless)
- .env (untracked), .env.example, .gitignore, phpunit.xml
- README.md, .agent/PROJECT.md, .agent/DECISIONS/ADR-013-stateless-no-database.md (new)
- tests/Feature/ContactFormTest.php (rewritten), tests/Feature/StatelessArchitectureTest.php (new)
- .agent/TASKS/TASK-011-stateless-storefront.md (new), .agent/RUNS/…013.ledger.md (new),
  .agent/STATE/SNAPSHOT-…013.md (new), .agent/OBSERVATIONS/OBS-017… (new)

## Commits Created
- 8bced93 — feat(stateless): remove the database layer entirely
- 66971a7 — fix(security): remove /__debug, untrack .env, throttle contact form
- 5ce315a — test(architecture): guard the stateless contract

## Branches
- feature/TASK-011-stateless-storefront (work), main (integration)

## Merge Status
- MERGED locally: main @ aa94ca2 (verified: 17 tests / 120 assertions).
- NOT PUSHED: remote origin/main remains at ecde2d5 (403 on push).

## Verification
- Baseline before changes: php artisan test — 14 passed (101 assertions): PASS
- After C1: 14 passed (103): PASS
- After C2: 14 passed (103): PASS
- After C3: 17 passed (120 assertions): PASS (final suite)
- sh -n docker/entrypoint.sh + entrypoint.frankenphp.sh: PASS
- php artisan route:list: no /__debug, no DB routes: PASS
- git ls-files | grep '^\.env$': empty: PASS
- Full suite re-run on main after merge: PASS
- Vercel/Render runtime paths: verified by static review + shell syntax +
  route/artisan smoke checks; no Docker daemon or Vercel CLI in this
  sandbox (OBS-004), so image builds were NOT executed here. Deployment
  risk assessed as low: entrypoint changes are removals plus a no-write
  env fallback whose pieces (artisan boot under Vercel env, key:generate)
  were already exercised by the previously working migrate step.

## Discoveries
- OBS-017: sandbox is ephemeral across sessions; tools/ must be
  re-provisioned per run (recipe in OBS-002 still valid, PHP 8.4.8 now).
- The committed .env was load-bearing on Vercel (only APP_KEY source);
  untracking required the entrypoint key-generation step to avoid
  "No application encryption key" at cold start.
- `cp .env.example .env` in the Vercel entrypoint was a latent crash
  (set -e + read-only FS) that never fired only because the tracked .env
  shipped in the image; replaced with direct sourcing (no writes).
- A stale vendor/composer classmap (after deleting a class) makes Laravel
  tests throw ErrorException on the autoload include; fresh clones are
  unaffected (composer install rebuilds the map).

## Decisions
- ADR-013: stateless storefront, no database; .env untracked with
  per-environment APP_KEY generation; /__debug removed; contact form
  throttled. Supersedes ADR-001.

## Problems / Blockers
- PUSH BLOCKED: the provided fine-grained GitHub token authenticates as
  Nexusdeveloper902 but was denied write access to this repository
  (403, Contents:write missing). Remedy: grant Contents:read/write on
  B2B-Marketplace for the token, or push manually:
    git push origin main feature/TASK-011-stateless-storefront
  (from a clone of this run's working tree).
- Residual risk: container-image builds could not be exercised in this
  sandbox (no Docker daemon — OBS-004); mitigations listed under
  Verification.

## Remaining Work
- Optional: purge `.env` from git history (requires history rewrite — out
  of scope per protocol §7.12 without explicit authorization) or rotate
  any credential that was ever placed in it.
- Optional cleanup carried over from SNAPSHOT-012: unused Apache/Vercel
  files (docker/apache/vhost.vercel.conf, docker/entrypoint.vercel.sh).

## Next Agent Notes
- The storefront has NO database and NO migrations. Do not reintroduce
  either without a new ADR superseding ADR-013.
- Vercel entrypoint: keep the `set -a; . ./.env (or .env.example); set +a`
  block and the step-2 storage overrides exactly as they are (OBS-013,
  OBS-014, OBS-015 still apply). APP_KEY generation (step 3) is the
  replacement for the old migrate step — do not remove unless APP_KEY is
  provided via Vercel project settings.
- Render entrypoint keeps `cp .env.example .env` + `key:generate --force`
  (writable FS there) — that is intentional, not an oversight.
- Contact leads live in the platform logs only (stderr on Vercel,
  storage/logs/laravel.log on Render).
