# ADR-007

## Date
2026-09-02

## Context
RUN-2026-09-02-marketplace-005 (ADR-006) shipped a Vercel deployment
variant consisting of:
- `Dockerfile.vercel` — the Vercel-shaped Dockerfile
- `docker/apache/vhost.vercel.conf` — vhost template with `{{PORT}}`
- `docker/entrypoint.vercel.sh` — Vercel-specific entrypoint
- `vercel.json` — wiring Vercel's `@vercel/docker` builder at
  `Dockerfile.vercel`

The user imported the repo into Vercel and Vercel's build failed with:

```
> Installing Builder: @vercel/docker
Error: The package `@vercel/docker` is not published on the npm registry
```

Vercel's `@vercel/docker` builder — the legacy way to opt into Vercel
Container Deployments via `vercel.json`'s `builds` array — has been
deprecated and removed from the npm registry. The replacement is
Vercel's native Dockerfile auto-detection: a `Dockerfile` in the repo
root is automatically built as a container deployment, no `vercel.json`
required. See OBS-008 for the full evidence.

## Decision
Switch to Vercel's native Dockerfile auto-detection by:

1. **Renaming `Dockerfile.vercel` → `Dockerfile`** so Vercel auto-detects
   it. The Vercel-specific contents (port templating, ephemeral-FS
   relocation, conditional chown) are unchanged from ADR-006.
2. **Renaming the original Render-shaped `Dockerfile` → `Dockerfile.render`**
   so the file previously known as the repo's default Dockerfile is
   still available for the Render / docker-compose deployment target.
3. **Deleting `vercel.json`** — no longer needed; was the source of
   the failure.
4. **Updating `docker-compose.yml`** to reference `Dockerfile.render`
   explicitly (compose's default is `Dockerfile`, which would now
   build the Vercel variant — wrong for the persistent-volume Render
   deployment).
5. **Updating `.dockerignore`** to exclude both `Dockerfile` and
   `Dockerfile.render` from each image's build context (so neither
   image carries stale deployment-target files in its `/var/www/html`).
6. **Updating `README.md`** with the new filenames and a note that
   Render's dashboard Dockerfile path must be set to `Dockerfile.render`.

The Vercel-specific files (`docker/apache/vhost.vercel.conf`,
`docker/entrypoint.vercel.sh`) keep their `.vercel` suffix — they're
referenced by name inside `Dockerfile` (now the Vercel variant) and
renaming them would require coordinated edits across multiple files
for no real benefit. The naming asymmetry (Dockerfile vs. vhost.vercel.conf)
is a minor wart documented here.

## Alternatives Considered
- **Stay on `@vercel/docker` via an older published version.** Rejected:
  the package is gone from the npm registry, not just deprecated.
  `npm install @vercel/docker` fails outright; there is no version to
  pin to.
- **Use a community fork of `@vercel/docker`.** Rejected: introduces
  an external unmaintained dependency for no benefit over native
  auto-detection. Also a security/supply-chain concern.
- **Switch to Vercel's `@vercel/static-build` or `@vercel/php` builder.**
  Rejected: `@vercel/php` is serverless-only (no long-running Apache,
  no SQLite persistence even within a single container lifetime), and
  `@vercel/static-build` produces a static site, not a PHP app. The
  app genuinely needs a container runtime.
- **Abandon Vercel as a deployment target.** Rejected: the user
  explicitly asked for Vercel deployment; the original Render deployment
  (ADR-004) is still working and unaffected by this run.

## Reasoning
Vercel's native Dockerfile auto-detection is the supported replacement
for the deprecated `@vercel/docker` builder. It requires no `vercel.json`
and no build-time configuration beyond a `Dockerfile` in the repo root.
The only cost is the file rename — the Vercel-shaped Dockerfile becomes
the repo's default, and the Render-shaped Dockerfile takes a `.render`
suffix. Both deployment targets continue to work with isolated, clearly-
named Dockerfiles.

The `.dockerignore` update keeps both images clean: each one excludes
its own Dockerfile name plus the other variant's Dockerfile name, so
neither image carries the other's deployment-target file in its
`/var/www/html` tree.

## Consequences
- Vercel import now works without `vercel.json`. Vercel auto-detects
  `Dockerfile` and builds it as a container deployment.
- The Render deployment requires updating Render's dashboard "Dockerfile
  path" setting from the default `Dockerfile` to `Dockerfile.render`,
  OR the user can use `docker-compose.yml` locally (which already
  references `Dockerfile.render` after this run).
- Future agents must NOT re-introduce `vercel.json` with a `builds`
  array pointing at `@vercel/docker` — that builder no longer exists.
- Future agents must NOT rename `Dockerfile` back to `Dockerfile.vercel`
  — Vercel would no longer auto-detect it.
- ADR-006 (which described the `vercel.json` + `@vercel/docker` approach)
  is superseded by this ADR but remains in the repo as a historical
  record per the append-only protocol.
- If a future Vercel change re-introduces a `vercel.json` requirement
  (e.g. for custom build settings, regions, or function configuration),
  a new ADR should be written superseding this one.

## Status
ACTIVE

## Supersedes
ADR-006-vercel-deployment.md (the `vercel.json` + `@vercel/docker` builder
approach — the builder is no longer published to the npm registry; see
OBS-008)
