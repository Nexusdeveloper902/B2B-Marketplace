# ADR-011

## Date
2026-09-02

## Context
After five failed attempts (RUN-005 through RUN-009) to deploy the
storefront on Vercel using Apache+mod_php, deep research revealed that
Apache+mod_php is fundamentally the wrong runtime for Vercel Container
Deployments.

The specific failure mode (OBS-012): the official `php:apache` Docker
image does NOT pass OS environment variables to PHP by default. This
means `DB_DATABASE` (set by the entrypoint) never reaches Laravel's
`env()` function. Combined with Vercel's read-only image layer at request
time, this causes the persistent `DatabaseManager.php line 226` crash.

Vercel's official PHP deployment guide
(`vercel.com/kb/guide/deploy-php-on-vercel-with-docker`) uses
**FrankenPHP** — a single binary combining the Caddy web server and the
PHP runtime. FrankenPHP:
- Reads OS env vars natively (no `SetEnv`/`PassEnv` needed)
- Binds to `$PORT` via Caddyfile config (`:{$PORT:80}` syntax)
- Serves PHP directly (no mod_php, no FastCGI)
- Is the officially recommended PHP runtime for Vercel Container Deployments

## Decision
Switch the Vercel Dockerfile from `php:8.4-apache` to
`dunglas/frankenphp:1-php8.4`. This involves:

1. **New `Dockerfile.vercel`** based on `dunglas/frankenphp:1-php8.4`:
   - Stage 1: composer install (unchanged)
   - Stage 2: FrankenPHP runtime + `install-php-extensions pdo_sqlite sqlite3`
   - Copies Caddyfile to `/etc/frankenphp/Caddyfile`
   - CMD: `frankenphp run --config /etc/frankenphp/Caddyfile`

2. **New `docker/caddy/Caddyfile.vercel`**:
   ```
   :{$PORT:80} {
       root * /app/public
       encode zstd gzip
       php_server {
           try_files {path} /index.php
       }
   }
   ```
   The `:{$PORT:80}` syntax reads `$PORT` from the environment (Vercel
   injects it) and falls back to 80 for local dev. No entrypoint port
   patching needed.

3. **New `docker/entrypoint.frankenphp.sh`** (simplified):
   - Creates writable dirs in `/tmp` (ephemeral FS)
   - Symlinks `storage/` to `/tmp/storage`
   - Creates SQLite file at `/tmp/storefront/database.sqlite`
   - `export DB_DATABASE=$DB_FILE` (FrankenPHP reads this natively!)
   - Generates APP_KEY if missing
   - Runs migrations
   - Execs `frankenphp run`

4. **Updated `config/database.php`**: simplified the SQLite path fallback
   to check `is_dir()` instead of `is_writable()` (which returns true for
   root even on read-only filesystems).

5. **Render deployment is UNCHANGED**: `Dockerfile` (Apache+mod_php) +
   `docker/entrypoint.sh` + `docker/apache/vhost.conf` remain for the
   Render/docker-compose target. The two deployments now use different
   web servers — that's intentional and documented.

## Alternatives Considered
- **Add `SetEnv`/`PassEnv` directives to the Apache vhost.** Rejected:
  this would fix the env var passing, but Apache+mod_php still has the
  port templating issue, the read-only filesystem issue, and is not
  what Vercel officially supports. FrankenPHP is the better long-term
  choice.

- **Switch to php-fpm + nginx.** Rejected: more moving parts (two
  processes), and still has the env var passing issue (php-fpm also
  doesn't pass OS env vars by default — see docker-library/php issue #74).

- **Use Vercel's `@vercel/php` serverless runtime.** Rejected: serverless
  PHP has a 250MB limit, no long-running processes, no SQLite persistence
  even within a single request. The app needs a container runtime.

- **Stay with Apache+mod_php and keep debugging.** Rejected: the user
  explicitly requested deep research after five failed attempts. The
  research clearly shows FrankenPHP is the right choice. Continuing to
  debug Apache+mod_php wastes time.

## Reasoning
FrankenPHP is Vercel's officially recommended PHP runtime for container
deployments. It eliminates all the issues that plagued the Apache+mod_php
approach:
- Env vars are read natively (no SetEnv/PassEnv)
- Port is configured via Caddyfile (no entrypoint patching)
- Single binary (simpler image, fewer moving parts)
- Production-ready and actively maintained

The Render deployment stays on Apache+mod_php because it works fine
there (writable filesystem, no env var passing issue because the entrypoint
materializes to `.env` which is read correctly on Render's filesystem).

## Consequences
- The Vercel deployment should now work correctly (pending user-side
  redeploy verification).
- The Dockerfile.vercel no longer uses Apache — it uses FrankenPHP/Caddy.
- The old Apache-based Vercel files (`docker/apache/vhost.vercel.conf`,
  `docker/entrypoint.vercel.sh`) remain in the repo but are unused by
  the Vercel deployment. They can be removed in a future cleanup.
- `config/database.php` has a simplified fallback that checks `is_dir()`
  instead of `is_writable()`.
- The `vercel.json` (services + runtime:container + rewrites) from
  ADR-010 remains unchanged and is still required.
- Future agents must NOT revert to Apache+mod_php for the Vercel
  deployment. FrankenPHP is the correct runtime.

## Status
ACTIVE

## Supersedes
none (ADR-010's `services` config is still correct and required; this ADR
changes the Dockerfile content from Apache to FrankenPHP but keeps the
same `vercel.json` structure)
