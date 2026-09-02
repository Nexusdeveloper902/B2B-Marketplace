# RUN RUN-2026-09-02-marketplace-010

## Task
TASK-009-switch-to-frankenphp.

## Agent Role
Full-Stack Engineer (Laravel / Blade) — switch to FrankenPHP.

## Result
COMPLETED (code shipped + pushed; Vercel live verification pending user
redeploy).

## Resume Notes
- Repository state: clean `main` at `2dd035b`.
- RUN-009 fixed the `services` config so Vercel now runs the Docker image,
  but the `DatabaseManager.php line 226` crash persisted — meaning Laravel
  IS running but the DB connection fails.
- Deep research (web-search + web-reader) into Vercel's official PHP
  guide, FrankenPHP docs, and the php:apache env var passing issue.

## Summary
Switched the Vercel Dockerfile from Apache+mod_php to FrankenPHP (Vercel's
officially recommended PHP runtime). FrankenPHP reads env vars natively,
eliminating the root cause of all previous failures: Apache's inability to
pass OS env vars to PHP.

## Changes Made
- New `Dockerfile.vercel` (FrankenPHP-based)
- New `docker/caddy/Caddyfile.vercel`
- New `docker/entrypoint.frankenphp.sh` (simplified)
- Updated `config/database.php` (simplified fallback)
- Updated README
- Render deployment unchanged

## Files Changed
- Dockerfile.vercel (rewritten)
- docker/caddy/Caddyfile.vercel (new)
- docker/entrypoint.frankenphp.sh (new)
- config/database.php (modified)
- README.md (modified)
- .agent/OBSERVATIONS/OBS-012-apache-mod-php-env-var-passing.md (new)
- .agent/DECISIONS/ADR-011-switch-to-frankenphp.md (new)
- .agent/TASKS/TASK-009-switch-to-frankenphp.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-010.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-010.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-010.md (new)

## Commits Created
- (hash recorded after commit)

## Verification
- sh -n docker/entrypoint.frankenphp.sh: PASS
- Static review: PASS
- Vercel live verification: PENDING

## Discoveries
- OBS-012: php:apache doesn't pass OS env vars to PHP. Root cause of all
  previous failures.

## Decisions
- ADR-011: switch to FrankenPHP.

## Remaining Work
- User: redeploy on Vercel. Visit /__debug to confirm DB path.

## Next Agent Notes
- Do NOT revert to Apache+mod_php for Vercel. FrankenPHP is correct.
- The old Apache-based Vercel files (docker/apache/vhost.vercel.conf,
  docker/entrypoint.vercel.sh) are unused — can be cleaned up later.
