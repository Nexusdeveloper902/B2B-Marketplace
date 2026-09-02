# OBS-011

## Date
2026-09-02

## Observation
After FOUR attempts to fix the Vercel deployment (RUN-005 through
RUN-008), the `DatabaseManager.php line 226` crash persisted with
identical error logs across different deployment IDs. The user
correctly identified that the fixes were having ZERO effect and
requested deep research.

Deep research into Vercel's official documentation (specifically
`vercel.com/kb/guide/deploy-php-on-vercel-with-docker` and
`vercel.com/kb/guide/docker`) revealed the actual root cause:

**Vercel does NOT auto-detect a bare `Dockerfile` as a container
deployment.** A bare `Dockerfile` in the repo root is treated as a
generic serverless function (with different semantics, different
filesystem model, no guaranteed ENTRYPOINT execution, and a 250MB
unzipped limit). To deploy a Docker image as a proper container
service on Vercel, you need ALL THREE of:

1. **The Dockerfile must be named `Dockerfile.vercel`** (or
   `Containerfile.vercel`). Vercel's container runtime specifically
   detects this filename, NOT a bare `Dockerfile`.

2. **A `vercel.json` with a `services` key** declaring the container
   service:
   ```json
   {
     "services": {
       "api": {
         "root": ".",
         "entrypoint": "Dockerfile.vercel",
         "runtime": "container"
       }
     }
   }
   ```
   The `runtime: "container"` is what tells Vercel to run it as a
   long-running container (not a serverless function).

3. **A `rewrites` catch-all** routing traffic to the service:
   ```json
   {
     "rewrites": [
       { "source": "/(.*)", "destination": { "service": "api" } }
     ]
   }
   ```

Without this configuration, Vercel falls back to **framework
auto-detection** — it detects Laravel via `composer.json` and uses its
own PHP runtime (or treats the Dockerfile as a serverless function).
In EITHER fallback case, the Dockerfile/entrypoint changes have NO
EFFECT because Vercel isn't running the Docker image the way the
Dockerfile specifies.

## Evidence
- Vercel official PHP guide (`vercel.com/kb/guide/deploy-php-on-vercel-with-docker`):
  > "Add a Dockerfile.vercel and set the service runtime to container.
  > Vercel builds the image, stores it in the Vercel Container Registry,
  > and serves it from a Function that autoscales with traffic and
  > scales to zero when idle."

  The guide's `vercel.json`:
  ```json
  {
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

- Vercel Docker guide (`vercel.com/kb/guide/docker`):
  > "Add a Dockerfile.vercel (or Containerfile.vercel) to your project
  > root, and Vercel builds the image, stores it in VCR, and serves it
  > from a Function that scales automatically with traffic."

- Vercel ASP.NET guide (`vercel.com/kb/guide/dot-net-asp-net-on-vercel-with-docker`):
  > "vercel.json declares one service with `runtime: 'container'`
  > pointing at that Dockerfile, plus a catch-all rewrite sending every
  > request to it."

- User-reported error persistence across RUN-005 through RUN-008:
  - RUN-005: `@vercel/docker` builder deprecated (OBS-008)
  - RUN-006: removed `vercel.json`, renamed `Dockerfile.vercel` → `Dockerfile`
    (WRONG — Vercel doesn't treat a bare `Dockerfile` as a container service)
  - RUN-007: `.env` materialization fix (ADR-008) — no effect
  - RUN-008: config-level fallbacks (ADR-009) — no effect
  - All four runs produced the SAME `DatabaseManager.php line 226` error

## Impact
- All previous Vercel fixes (RUN-006 through RUN-008) had zero effect
  because Vercel was never running the Docker image — it was falling
  back to framework auto-detection or treating the Dockerfile as a
  serverless function.
- The config-level fallbacks from ADR-009 (config/database.php,
  config/session.php, config/cache.php) are still valuable as a
  secondary layer — they'll help once Vercel is actually running the
  Docker image — but they were never the primary fix.
- The `.env` materialization from ADR-008 is also still valuable as
  a secondary layer.
- ADR-007 (which assumed Vercel auto-detects a bare `Dockerfile`) is
  SUPERSEDED by ADR-010 (which uses the correct `services` +
  `runtime: "container"` approach).

## Related Task
TASK-008-vercel-services-config

## Status
CONFIRMED — by Vercel's official documentation (PHP guide, Docker guide,
ASP.NET guide) and by the user-reported persistence of identical errors
across four different fix attempts. The fix is applied in
RUN-2026-09-02-marketplace-009 via ADR-010.
