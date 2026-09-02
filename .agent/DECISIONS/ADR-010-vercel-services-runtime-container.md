# ADR-010

## Date
2026-09-02

## Context
After four failed attempts (RUN-005 through RUN-008), deep research into
Vercel's official documentation revealed that the previous approaches
were fundamentally wrong about how Vercel handles Dockerfiles.

The chain of failures:
1. RUN-005: Used `vercel.json` with `builds: [{use: "@vercel/docker"}]`
   → Failed because `@vercel/docker` was deprecated (OBS-008)
2. RUN-006: Removed `vercel.json`, renamed `Dockerfile.vercel` → `Dockerfile`
   → WRONG: Vercel does NOT treat a bare `Dockerfile` as a container service
3. RUN-007: Fixed DB path via `.env` materialization → No effect (Vercel
   wasn't running the Docker image)
4. RUN-008: Fixed DB path via config-level fallbacks → No effect (same
   reason)

The actual Vercel container deployment model (per official docs at
`vercel.com/kb/guide/deploy-php-on-vercel-with-docker` and
`vercel.com/kb/guide/docker`) requires THREE things:
1. The Dockerfile named `Dockerfile.vercel` (not `Dockerfile`)
2. A `vercel.json` with `services` declaring `runtime: "container"`
3. A `rewrites` catch-all routing traffic to the service

## Decision
Revert RUN-006's file rename and use the correct Vercel container
service configuration:

1. **Rename `Dockerfile` → `Dockerfile.vercel`** (undoing RUN-006's
   rename). Vercel's container runtime detects `Dockerfile.vercel`
   specifically.

2. **Rename `Dockerfile.render` → `Dockerfile`** (undoing RUN-006's
   rename). The Render variant is now the repo's default `Dockerfile`
   again, used by `docker-compose.yml` and Render's default detection.

3. **Create a new `vercel.json`** with the correct `services` structure:
   ```json
   {
     "$schema": "https://openapi.vercel.sh/vercel.json",
     "services": {
       "api": {
         "root": ".",
         "entrypoint": "Dockerfile.vercel",
         "runtime": "container"
       }
     },
     "rewrites": [
       { "source": "/(.*)", "destination": { "service": "api" } }
     ]
   }
   ```
   This is the NEW `services` approach — completely different from the
   deprecated `builds` + `@vercel/docker` approach (OBS-008) and
   completely different from the "no vercel.json, bare Dockerfile"
   approach (ADR-007, which was wrong).

4. **Update `docker-compose.yml`** to reference `Dockerfile` (not
   `Dockerfile.render`).

5. **Update `.dockerignore`** to exclude both `Dockerfile` and
   `Dockerfile.vercel` from each image's build context.

6. **Keep the config-level fallbacks** from ADR-009
   (config/database.php, config/session.php, config/cache.php) and the
   `.env` materialization from ADR-008 — they're still valuable as
   secondary layers now that Vercel will actually run the Docker image.

7. **Keep the `/__debug` route** from RUN-008 — useful for diagnosing
   any remaining issues.

## Alternatives Considered
- **Switch to FrankenPHP** (as Vercel's PHP guide uses). Rejected:
  would require a completely new Dockerfile + Caddyfile, removing
  Apache/mod_php. The Apache setup works fine; the issue was never
  the web server, it was the Vercel configuration. Switching to
  FrankenPHP adds risk without addressing the root cause.

- **Stay with the bare `Dockerfile` approach (ADR-007) and keep
  debugging.** Rejected: the user explicitly asked for deep research
  after four failed attempts. The research definitively shows ADR-007's
  assumption (Vercel auto-detects a bare `Dockerfile`) is wrong.
  Continuing to debug on a wrong foundation wastes time.

- **Use Vercel's `@vercel/php` runtime instead of Docker.** Rejected:
  `@vercel/php` is serverless-only (no long-running Apache, no SQLite
  persistence even within a single container lifetime, 250MB limit).
  The app genuinely needs a container runtime.

## Reasoning
Vercel's container deployment model is specifically designed around
`Dockerfile.vercel` + `services` + `runtime: "container"`. This is
the supported, documented way to run a Docker image on Vercel. The
previous approaches were based on incorrect assumptions about Vercel's
auto-detection behavior.

The `services` + `runtime: "container"` declaration tells Vercel to:
1. Build the Dockerfile.vercel image
2. Store it in Vercel Container Registry
3. Serve it from a Vercel Function that runs as a long-running container
4. Scale it automatically with traffic (and scale to zero when idle)
5. Route traffic to it via the catch-all rewrite

This is fundamentally different from treating a bare `Dockerfile` as a
serverless function (which has different invocation semantics, a 250MB
limit, and no guaranteed ENTRYPOINT execution).

## Consequences
- Vercel will now actually build and run the Docker image as a
  container service — meaning all the entrypoint/config changes from
  RUN-007 and RUN-008 will finally take effect.
- The Vercel deployment should now serve requests successfully (pending
  user-side redeploy verification).
- The Render deployment is unaffected — `Dockerfile` is the Render
  variant again, `docker-compose.yml` references it, and no Render
  dashboard changes are needed.
- ADR-007 (which assumed Vercel auto-detects a bare `Dockerfile`) is
  SUPERSEDED by this ADR.
- ADR-006 (the `@vercel/docker` builder approach) remains superseded
  by ADR-007, which is now itself superseded by this ADR.
- Future agents must NOT rename `Dockerfile.vercel` to `Dockerfile` —
  Vercel requires the `.vercel` suffix.
- Future agents must NOT remove the `vercel.json` `services` declaration
  — without it, Vercel falls back to framework auto-detection.
- The `rewrites` catch-all is required — without it, Vercel doesn't
  route traffic to the container service.

## Status
ACTIVE

## Supersedes
ADR-007-vercel-default-dockerfile.md (which incorrectly assumed Vercel
auto-detects a bare `Dockerfile` — Vercel actually requires
`Dockerfile.vercel` + `services` + `runtime: "container"`)
