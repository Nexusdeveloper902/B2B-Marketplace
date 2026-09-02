# OBS-007

## Date
2026-09-02

## Observation
Vercel Container Deployments have an ephemeral filesystem: writes during
runtime are NOT preserved across cold starts (redeploys, scale-to-zero
cycles, container recycles). The container filesystem may also be
read-only in some Vercel configurations outside of `/tmp`.

For this storefront — which persists contact/demo requests to a single
SQLite file at `database/database.sqlite` (per ADR-001) — this means:

1. **Contact submissions are lost on cold restart.** Any submission made
   between container start and container recycle (which can happen
   silently on Vercel's free tier, on redeploy, or on traffic spikes
   that trigger new container instances) is destroyed when the container
   is replaced. The migration recreates the table on the next start.

2. **Sessions, view cache, and logs are also ephemeral.** The entrypoint
   relocates Laravel's `storage/` tree to `/tmp/storefront/storage` so
   the app actually runs (compiled Blade views, session files, cache
   files, log files all land in `/tmp`), but the relocation does not
   preserve state across cold restarts.

3. **`APP_KEY` regenerated on cold start (if not provided via Vercel env
   var)** invalidates any existing sessions and signed URLs. For a
   stateless marketing storefront with no auth and no signed URLs in
   use, this is a non-issue. For a future feature that uses signed URLs
   (e.g. password reset, shareable demo links), `APP_KEY` MUST be set
   via Vercel's dashboard env vars to persist across cold restarts.

The fix that makes the app *run* on Vercel at all (entrypoint relocation
to `/tmp`) is documented in ADR-006. The data-loss consequence is the
subject of this observation.

## Evidence
- Vercel's official docs on Container Deployments: "the filesystem is
  ephemeral and not persisted across deployments."
- Local reasoning: `/tmp` is the only directory the Linux Filesystem
  Hierarchy Standard guarantees is writable on every POSIX system;
  `/var/www/html` (where the app code lives) may be mounted read-only
  by Vercel's container runtime.
- Repository inspection: the existing `docker/entrypoint.sh` writes to
  `storage/framework/*`, `storage/logs`, and `database/database.sqlite`
  under `/var/www/html`. The Vercel variant `docker/entrypoint.vercel.sh`
  relocates these to `/tmp/storefront/...` — see the entrypoint source
  for the exact relocation logic.
- No PHP/Docker runtime available in this execution environment to run
  the Vercel image end-to-end and confirm cold-restart data loss
  empirically. This observation is based on Vercel's documented
  container semantics plus reasoning about the entrypoint's relocation
  logic; the data-loss claim is a direct consequence of Vercel's
  documented ephemeral FS, not a hypothesis requiring empirical
  verification.

## Impact
- The Vercel deployment is suitable for **demo / marketing purposes
  only**, not for collecting real contact submissions that need to
  survive past the next container recycle.
- For production contact collection, use the Render deployment (which
  has a persistent SQLite volume via `docker-compose.yml` per ADR-004)
  OR migrate the storefront to a hosted database (Vercel Postgres,
  Turso, Neon) — out of scope per ADR-001 and TASK-001 constraints.
- If a future agent adds signed URLs, password resets, or any feature
  that relies on `APP_KEY` persistence, they MUST set `APP_KEY` via
  Vercel's dashboard env vars; the entrypoint's auto-generation logic
  is for the demo-only zero-config path, not for stateful features.

## Related Task
TASK-004-vercel-deployment

## Status
CONFIRMED — by Vercel's documented container semantics and by direct
inspection of the entrypoint's relocation logic. Full empirical
verification (run the image, submit a contact form, force a cold
restart, confirm the submission is gone) requires a Vercel deployment
and is left to the user.
