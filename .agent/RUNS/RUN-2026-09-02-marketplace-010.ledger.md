# Activity Ledger — RUN-2026-09-02-marketplace-010

## Resume + deep research
- Pulled latest main (clean at 2dd035b).
- Parallel web searches for: Vercel Laravel Docker deployment, vercel.json
  services schema, FrankenPHP Laravel, 250MB limit, Apache env var passing.
- Fetched Vercel official docs: services, vercel.json, PHP guide, Docker guide.
- Fetched FrankenPHP docs: Docker, Laravel integration.
- Fetched blog: multi-stage Laravel+FrankenPHP Dockerfile.

## Root cause found
- OBS-012: php:apache image doesn't pass OS env vars to PHP by default.
  This is why DB_DATABASE never reached Laravel's env() — the root cause
  of all 5 previous failures.

## Implementation
- New Dockerfile.vercel: dunglas/frankenphp:1-php8.4 base, install-php-extensions
  pdo_sqlite sqlite3, Caddyfile config, simplified entrypoint.
- New Caddyfile.vercel: :{$PORT:80}, root /app/public, php_server with try_files.
- New entrypoint.frankenphp.sh: export DB_DATABASE (FrankenPHP reads natively).
- Simplified config/database.php fallback (is_dir instead of is_writable).
- Render deployment unchanged.

## Verify
- sh -n docker/entrypoint.frankenphp.sh: PASS
- Static review: PASS
- Vercel live: PENDING

## Commit + Push
- (hash recorded after commit)
