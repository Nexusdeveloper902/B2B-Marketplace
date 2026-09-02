# ADR-008

## Date
2026-09-02

## Context
After RUN-006 fixed the `@vercel/docker` builder deprecation, the Vercel
deployment still crashed on every request with `DatabaseManager.php
line 226` (OBS-009). Root cause: the Vercel entrypoint's `DB_DATABASE`
materialization only fired when the `DB_DATABASE` env var was explicitly
set. On Vercel, no such env var is set, so Laravel fell back to its
default SQLite path (`/var/www/html/database/database.sqlite`) — which
is in the read-only image layer at request time, causing the DB
connection to crash.

The Render entrypoint (`docker/entrypoint.sh`) has the same conditional
materialization, but it works on Render because Render's container
filesystem is writable at request time and the `chown` step makes the
default path accessible to `www-data`. Vercel's container runtime does
not provide either guarantee.

## Decision
In `docker/entrypoint.vercel.sh`, ALWAYS materialize
`DB_DATABASE=$DB_FILE` into `.env` (where `$DB_FILE` is the path the
entrypoint just created in step 2 — `/tmp/storefront/database.sqlite`
by default). Remove the `if [ -n "${DB_DATABASE:-}" ]` guard that
previously gated this step.

This forces Laravel — at both migrate-time (CLI) and request-time
(Apache mod_php) — to look at `/tmp/storefront/database.sqlite`, which
is:

1. **The same file the entrypoint created and migrated** — no path
   mismatch between entrypoint-time and request-time.
2. **In `/tmp`** — world-writable by definition; `www-data` can write
   to it without needing the `chown` step.
3. **Ephemeral but per-container-lifetime** — survives for the
   container's lifetime; cold restart wipes it, but the entrypoint
   re-creates and re-migrates it on every cold start (already
   documented in OBS-007).

The Render entrypoint is UNCHANGED — its conditional materialization
works correctly on Render and changing it would risk regressing the
Render deployment (which is currently working per RUN-004's forceScheme
fix). The two entrypoints now diverge on this point; that divergence is
intentional and documented in both files' comments.

## Alternatives Considered
- **Set `DB_DATABASE` as a Vercel dashboard env var.** Rejected: requires
  manual user setup outside the repo, and (per OBS-005) the mod_php web
  SAPI may not reliably expose container env vars to PHP anyway. The
  entrypoint's materialization into `.env` is the deterministic fix.
- **Change `config/database.php` to default to `/tmp/storefront/database.sqlite`
  instead of `database_path('database.sqlite')`.** Rejected: pollutes
  application config with deployment-target-specific paths; breaks local
  dev (`php artisan serve` would suddenly write the DB to `/tmp`); and
  the `.env` materialization is the Laravel-idiomatic way to override
  config values per-environment.
- **Make the Render entrypoint also unconditionally materialize
  `DB_DATABASE`.** Rejected: would change Render's working behavior and
  risk regression. Render's persistent-volume deployment deliberately
  relies on the `docker-compose.yml`-provided `DB_DATABASE` env var
  (per ADR-004) — unconditional materialization would override that.
- **Symlink `/var/www/html/database/database.sqlite` to `/tmp/storefront/database.sqlite`.**
  Rejected: adds a layer of indirection for no benefit; the `.env`
  materialization is simpler and matches Laravel's intended configuration
  flow.

## Reasoning
The bug is a path mismatch between entrypoint-time and request-time.
The fix is to make both times look at the same path. The simplest way
to do that in Laravel is to set `DB_DATABASE` in `.env` — which is
exactly what the existing materialization block was supposed to do,
except it was gated on a condition that doesn't hold on Vercel.

The /tmp path is the right choice because:

- `/tmp` is the only directory guaranteed writable on every POSIX
  system, regardless of which user the container runs as.
- It's already the location the entrypoint uses for the `storage/`
  symlink (per ADR-006), so the DB file lives alongside the rest of
  the ephemeral runtime state.
- It's per-container-lifetime, matching the ephemeral-FS consequence
  already documented in OBS-007.

## Consequences
- The Vercel deployment should now serve requests successfully (pending
  user-side redeploy verification).
- The Render deployment is unaffected — `docker/entrypoint.sh` is
  unchanged.
- The two entrypoints now diverge on the `DB_DATABASE` materialization
  logic. Future agents must NOT "fix" this divergence by making them
  match — the divergence is intentional and required by the different
  filesystem semantics of the two deployment targets.
- If a future agent moves the Vercel deployment to Vercel Persistent
  Storage (a paid feature that mounts a durable volume), the entrypoint
  would need to be updated to point `DB_FILE` at the mounted volume
  path instead of `/tmp/storefront/database.sqlite`. The unconditional
  materialization would still be correct — it would just materialize
  a different path.
- If a future agent adds a feature that needs persistent SQLite across
  cold restarts on Vercel, they must either use Vercel Persistent
  Storage or migrate to a hosted database (out of scope per ADR-001).

## Status
ACTIVE

## Supersedes
none (this ADR documents a fix to the Vercel entrypoint; ADR-006 and
ADR-007 remain ACTIVE for their respective concerns — ADR-006 for the
Vercel-shaped Dockerfile/vhost/entrypoint design, ADR-007 for the
Dockerfile auto-detection rename)
