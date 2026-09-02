# OBS-008

## Date
2026-09-02

## Observation
Vercel's `@vercel/docker` builder — the legacy way to opt a repo into
Vercel Container Deployments via a `vercel.json` `builds` array — was
deprecated and is no longer published to the npm registry. The Vercel
build now fails with:

```
> Installing Builder: @vercel/docker
Error: The package `@vercel/docker` is not published on the npm registry
```

The replacement is Vercel's native Dockerfile auto-detection: if a
repo has a `Dockerfile` in the root, Vercel builds it as a container
deployment with no `vercel.json` required. (A warning is also emitted
when `vercel.json` contains a `builds` key: "Due to `builds` existing
in your configuration file, the Build and Development Settings defined
in your Project Settings will not apply.")

## Evidence
- User-reported Vercel build log on 2026-09-02:
  ```
  > Installing Builder: @vercel/docker
  Error: The package `@vercel/docker` is not published on the npm registry
  ```
- The previous run (RUN-2026-09-02-marketplace-005) shipped a
  `vercel.json` with:
  ```json
  { "builds": [{ "src": "Dockerfile.vercel", "use": "@vercel/docker" }] }
  ```
  which triggered the failure on Vercel's next build.

## Impact
- Any repo that still ships a `vercel.json` with a `builds` array
  pointing at `@vercel/docker` will fail Vercel's build.
- The fix is to delete `vercel.json` and let Vercel auto-detect a
  `Dockerfile` in the repo root.
- For this repo, that means the Vercel-shaped Dockerfile must be named
  `Dockerfile` (not `Dockerfile.vercel`), and the original Render-shaped
  `Dockerfile` must be renamed (we use `Dockerfile.render`).
- `docker-compose.yml` must point at `Dockerfile.render` explicitly.
- Render's dashboard Dockerfile path setting must be updated to
  `Dockerfile.render` (otherwise Render will silently build the
  Vercel-shaped variant, which would work — both produce a working
  Apache+mod_php image — but the Vercel variant relocates state to
  /tmp which is wrong for Render's persistent-volume model).

## Related Task
TASK-005-vercel-docker-builder-fix

## Status
CONFIRMED — by user-reported Vercel build log. The fix is applied in
RUN-2026-09-02-marketplace-006; Vercel-side re-import verification
pending user retry.
