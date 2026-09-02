# TASK-009-switch-to-frankenphp

## Objective
Switch the Vercel Dockerfile from Apache+mod_php to FrankenPHP to resolve
the persistent `DatabaseManager.php line 226` crash caused by Apache's
inability to pass OS env vars to PHP.

## Origin
User request: "do a deeper audit of everything because the issues are
still happening... Maybe look up explicitly how to deploy Laravel using
Docker to Vercel or something."

## Root Cause
OBS-012: The `php:apache` Docker image does NOT pass OS env vars to PHP
by default. `DB_DATABASE` set by the entrypoint never reaches Laravel's
`env()`. Combined with Vercel's read-only image layer, this crashes every
request at `DatabaseManager.php line 226`.

## Implementation
- New `Dockerfile.vercel` based on `dunglas/frankenphp:1-php8.4`
- New `docker/caddy/Caddyfile.vercel` — listens on `:{$PORT:80}`
- New `docker/entrypoint.frankenphp.sh` — simplified, exports DB_DATABASE
- Updated `config/database.php` — simplified fallback (is_dir, not is_writable)
- Updated README — documents FrankenPHP switch
- Render deployment unchanged

## Acceptance Criteria
- [x] Dockerfile.vercel uses `dunglas/frankenphp:1-php8.4`
- [x] Caddyfile.vercel listens on `:{$PORT:80}`
- [x] entrypoint.frankenphp.sh exports DB_DATABASE (FrankenPHP reads it natively)
- [x] Render Dockerfile/entrypoint/vhost unchanged
- [x] vercel.json unchanged (services + runtime:container + rewrites)
- [x] ADR-011 + OBS-012 + TASK-009 + RUN-010 + ledger + snapshot written
- [x] Committed and pushed

## Commits

### Commit — {{COMMIT_HASH}}
See RUN-2026-09-02-marketplace-010 for details.
