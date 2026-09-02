# RUN RUN-2026-09-02-marketplace-006

## Task
TASK-005-vercel-docker-builder-fix.

## Agent Role
Full-Stack Engineer (Laravel / Blade) — Vercel build fix.

## Result
COMPLETED (code shipped + pushed; Vercel live build verification
pending user re-import).

## Resume Notes
- Repository state at resume: clean `main` at commit 3fce661 (hash
  backfill after RUN-005's Vercel variant addition).
- RUN-005 (commit 5dd4e48) shipped:
  - `Dockerfile.vercel` (Vercel-shaped Dockerfile)
  - `docker/apache/vhost.vercel.conf` (port-template vhost)
  - `docker/entrypoint.vercel.sh` (Vercel-specific entrypoint)
  - `vercel.json` (wired Vercel's `@vercel/docker` builder at
    `Dockerfile.vercel`)
- ADR-006 documented the `vercel.json` + `@vercel/docker` approach.
- OBS-007 documented the Vercel ephemeral-filesystem consequence.
- User imported the repo into Vercel on 2026-09-02 and the build failed:
  ```
  > Installing Builder: @vercel/docker
  Error: The package `@vercel/docker` is not published on the npm registry
  ```
- Diagnosis: Vercel's `@vercel/docker` builder has been deprecated and
  removed from the npm registry. The replacement is Vercel's native
  Dockerfile auto-detection (a `Dockerfile` in the repo root is built
  as a container deployment, no `vercel.json` required). Recorded in
  OBS-008.

## Summary
Switched from the deprecated `@vercel/docker` builder to Vercel's
native Dockerfile auto-detection by:
1. Deleting `vercel.json` (the source of the failure).
2. Renaming `Dockerfile.vercel` → `Dockerfile` so Vercel auto-detects it.
3. Renaming the original Render `Dockerfile` → `Dockerfile.render` so
   the Render / docker-compose deployment target continues to work.
4. Updating `docker-compose.yml` to reference `Dockerfile.render`
   explicitly.
5. Updating `.dockerignore` to exclude both Dockerfile variants from
   each other's build context.
6. Updating `README.md` to reflect the new filenames and document the
   Render-dashboard Dockerfile-path setting.

The Vercel-shaped Dockerfile's contents (port templating, ephemeral-FS
relocation, conditional chown) are unchanged from ADR-006 / OBS-007.
The Render Dockerfile's contents are unchanged (only header comments
were updated in both files).

## Changes Made
- Deleted `vercel.json`.
- Renamed `Dockerfile.vercel` → `Dockerfile` (Vercel auto-detects).
- Renamed original Render `Dockerfile` → `Dockerfile.render`.
- Updated `Dockerfile` header comment to reference OBS-008 and ADR-007.
- Updated `Dockerfile.render` header comment to document the rename and
  the Render-dashboard Dockerfile-path requirement.
- Updated `docker-compose.yml` to reference `Dockerfile.render`.
- Updated `.dockerignore` to exclude both `Dockerfile` and
  `Dockerfile.render` from each image's build context.
- Updated `README.md` to reflect new filenames, document the Render-
  dashboard Dockerfile-path setting, and remove references to the
  deleted `vercel.json`.
- Added `.agent/OBSERVATIONS/OBS-008-vercel-docker-builder-deprecated.md`.
- Added `.agent/DECISIONS/ADR-007-vercel-default-dockerfile.md`
  (supersedes ADR-006).
- Added `.agent/TASKS/TASK-005-vercel-docker-builder-fix.md`.
- Added this run record + its activity ledger.
- Added `.agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-006.md`.

## Files Changed
- Dockerfile (renamed from Dockerfile.vercel; header comment updated)
- Dockerfile.render (renamed from Dockerfile; header comment updated)
- vercel.json (deleted)
- docker-compose.yml (modified — references Dockerfile.render)
- .dockerignore (modified — excludes both variants)
- README.md (modified — new filenames + Render-dashboard note)
- .agent/OBSERVATIONS/OBS-008-vercel-docker-builder-deprecated.md (new)
- .agent/DECISIONS/ADR-007-vercel-default-dockerfile.md (new)
- .agent/TASKS/TASK-005-vercel-docker-builder-fix.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-006.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-006.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-006.md (new)

## Commits Created
- 9f98580 — fix(vercel): switch from deprecated @vercel/docker builder to native auto-detection

## Branches
- main (direct commit — single coherent fix-the-Vercel-build unit of
  work, no feature branch warranted)

## Merge Status
- Not applicable — committed directly to main. No feature branch.

## Verification
- `git mv Dockerfile.vercel Dockerfile`: PASS (confirmed via
  `git status --short`).
- `git mv Dockerfile Dockerfile.render`: PASS.
- `git rm vercel.json`: PASS (file no longer exists).
- Static review of `Dockerfile` (newly the Vercel variant) vs the
  pre-rename `Dockerfile.vercel`: PASS — same multi-stage shape, same
  stage-1 deps step, same stage-2 runtime setup, same Vercel-specific
  vhost and entrypoint references. Only the header comment changed.
- Static review of `Dockerfile.render` vs the pre-rename `Dockerfile`
  (Render variant): PASS — identical except for the header comment.
- `docker-compose.yml` review: PASS — references `Dockerfile.render`
  explicitly.
- `.dockerignore` review: PASS — both `Dockerfile` and
  `Dockerfile.render` excluded; no stale references to `Dockerfile.vercel`
  or `vercel.json`.
- README review: PASS — new filenames documented, Render-dashboard
  note present, Vercel section reflects auto-detection (no `vercel.json`).
- Orphan references in `.agent/` historical records (RUN-005,
  SNAPSHOT-005, ADR-006, TASK-004): these reference the now-deleted
  `Dockerfile.vercel` and `vercel.json` but are append-only per protocol
  Section 1.2 and must NOT be modified. They remain as historical
  context. ADR-006 is superseded by ADR-007 but is preserved unchanged.
- Vercel live build verification: PENDING (no Vercel CLI in this
  environment; user must trigger a fresh Vercel build by re-importing
  the repo or pushing a new commit).

## Discoveries
- OBS-008: Vercel's `@vercel/docker` builder has been deprecated and
  removed from the npm registry. The replacement is native Dockerfile
  auto-detection. Any repo still shipping a `vercel.json` with a
  `builds` array pointing at `@vercel/docker` will fail Vercel's build
  with "The package `@vercel/docker` is not published on the npm
  registry."
- Vercel's native Dockerfile auto-detection requires the file to be
  named exactly `Dockerfile` (case-sensitive) in the repo root. No
  `vercel.json` is needed in this mode.
- Render's dashboard Dockerfile-path setting defaults to `Dockerfile`;
  after this rename, the user must update that setting to
  `Dockerfile.render` for the Render deployment to keep building the
  Render variant (not the Vercel variant).

## Decisions
- ADR-007: switch from `vercel.json` + `@vercel/docker` builder to
  native Dockerfile auto-detection via file rename. Rejected
  alternatives: stay on `@vercel/docker` (package is gone from npm),
  use a community fork (security/supply-chain concern), use
  `@vercel/php` or `@vercel/static-build` (wrong shape — app needs a
  long-running container, not serverless PHP or a static site),
  abandon Vercel as a target (user explicitly asked for it).

## Problems / Blockers
- Cannot run Vercel CLI locally — Vercel live build verification must
  be done by the user via re-import or push.
- Render users must update their dashboard's Dockerfile-path setting
  to `Dockerfile.render`. This is documented in the README and in
  `Dockerfile.render`'s header comment, but cannot be enforced from
  the repo itself.

## Remaining Work
- User: re-import the repo into Vercel (or trigger a fresh build on
  the existing Vercel project). The build should now succeed without
  the `@vercel/docker` builder error.
- User: if a Render deployment exists, update its dashboard
  "Dockerfile path" setting from the default `Dockerfile` to
  `Dockerfile.render`. (Local `docker compose up` already works
  correctly because docker-compose.yml references `Dockerfile.render`
  explicitly.)

## Next Agent Notes
- The repo's default `Dockerfile` is now the Vercel variant (Apache +
  mod_php + SQLite with $PORT patching and /tmp ephemeral-FS
  relocation per ADR-006 / OBS-007). Do NOT rename it back to
  `Dockerfile.vercel` — Vercel would no longer auto-detect it.
- The Render / docker-compose deployment target is `Dockerfile.render`.
  Do NOT rename it back to `Dockerfile` — Vercel would then auto-detect
  the wrong variant (the Render variant, which binds port 80 only
  and doesn't relocate writable state to /tmp).
- `vercel.json` is intentionally absent. Do NOT re-introduce it with
  a `builds` array pointing at `@vercel/docker` — that builder is no
  longer published to the npm registry (OBS-008).
- If Vercel re-introduces a `vercel.json` requirement for custom build
  settings, write a new ADR superseding ADR-007.
- ADR-006 (the `vercel.json` + `@vercel/docker` approach) is
  superseded by ADR-007 but is preserved unchanged per the append-only
  protocol. Historical records in RUN-005 / SNAPSHOT-005 / TASK-004
  still reference `Dockerfile.vercel` and `vercel.json` — these are
  immutable historical context, not orphans to be cleaned up.
