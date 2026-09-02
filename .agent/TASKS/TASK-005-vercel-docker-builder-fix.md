# TASK-005-vercel-docker-builder-fix

## Objective
Fix the Vercel build failure caused by the deprecated `@vercel/docker`
builder package no longer being published to the npm registry. Switch
to Vercel's native Dockerfile auto-detection by renaming files so the
Vercel-shaped Dockerfile is the repo's default `Dockerfile`.

## Origin
User-reported Vercel build error on 2026-09-02:
```
> Installing Builder: @vercel/docker
Error: The package `@vercel/docker` is not published on the npm registry
```
This was the direct consequence of RUN-2026-09-02-marketplace-005's
`vercel.json` shipping a `builds` array pointing at `@vercel/docker`
(ADR-006). The builder has since been deprecated and removed.

## Requirements
- Delete `vercel.json` — it was the source of the failure.
- Rename `Dockerfile.vercel` → `Dockerfile` so Vercel auto-detects it
  natively.
- Rename `Dockerfile` → `Dockerfile.render` so the Render / docker-compose
  deployment target continues to work.
- Update `docker-compose.yml` to reference `Dockerfile.render` explicitly.
- Update `.dockerignore` so both images exclude their own and the other's
  Dockerfile from the build context.
- Update `README.md` to reflect the new filenames and document the
  Render-dashboard Dockerfile-path setting.
- Do NOT modify the Vercel-shaped Dockerfile's contents, the Vercel
  entrypoint, or the Vercel vhost — those are correct (ADR-006 / OBS-007).
- Do NOT modify the Render Dockerfile's contents beyond the header
  comment — the Render deployment is still working.
- Document the deprecation in `.agent/OBSERVATIONS/OBS-008-*` and the
  new approach in `.agent/DECISIONS/ADR-007-*` (superseding ADR-006).

## Acceptance Criteria
- [x] `vercel.json` is deleted.
- [x] `Dockerfile.vercel` is renamed to `Dockerfile` (Vercel auto-detects
      this filename; no `vercel.json` required).
- [x] The original Render `Dockerfile` is renamed to `Dockerfile.render`.
- [x] `docker-compose.yml` references `Dockerfile.render` explicitly.
- [x] `.dockerignore` excludes both `Dockerfile` and `Dockerfile.render`
      from each image's build context.
- [x] `README.md` documents the new filenames and the Render-dashboard
      Dockerfile-path setting.
- [x] ADR-007 records the decision and explicitly supersedes ADR-006.
- [x] OBS-008 records the deprecation evidence.
- [x] The Vercel-shaped Dockerfile contents (port templating,
      ephemeral-FS relocation, conditional chown) are unchanged from
      ADR-006.
- [x] The Render-shaped Dockerfile contents are unchanged (only the
      header comment is updated to reflect the new filename).
- [x] No changes to: application code, configuration, migrations, models,
      controllers, Blade templates, language files, `docker/apache/vhost.conf`,
      `docker/entrypoint.sh`, `docker/apache/vhost.vercel.conf`,
      `docker/entrypoint.vercel.sh`, `docker/php/opcache.ini`.
- [x] Fix is committed to `main` and pushed.

## Implementation
File operations:
- `git rm vercel.json` — removed the source of the build failure.
- `git mv Dockerfile.vercel Dockerfile` — Vercel auto-detects this name.
- `git mv Dockerfile Dockerfile.render` — Render/docker-compose deployment
  target.

Content updates:
- `Dockerfile` (newly the Vercel variant) — header comment rewritten to
  reflect that it's now the primary Dockerfile, not a `.vercel` variant;
  references updated to point at OBS-008 and ADR-007.
- `Dockerfile.render` — header comment updated to reflect the rename and
  document the Render-dashboard Dockerfile-path requirement.
- `docker-compose.yml` — `dockerfile: Dockerfile` →
  `dockerfile: Dockerfile.render`.
- `.dockerignore` — removed stale `Dockerfile.vercel` and `vercel.json`
  entries; added `Dockerfile.render` exclusion.
- `README.md` — "Deploy with Docker" section now references
  `Dockerfile.render`; "Deploy on Vercel" section updated to reflect
  that `vercel.json` is no longer needed and `Dockerfile` is auto-detected.

## Verification
- `git mv` operations succeeded — confirmed via `git status --short`.
- `git rm vercel.json` succeeded — file no longer exists.
- Static review of `Dockerfile` (newly the Vercel variant) — PASS:
  same multi-stage shape as ADR-006's `Dockerfile.vercel`, same stage-1
  dependencies step, same stage-2 runtime setup, same Vercel-specific
  vhost and entrypoint references. Only the header comment changed.
- Static review of `Dockerfile.render` — PASS: identical to the
  pre-rename `Dockerfile` (the Render variant per ADR-004) except for
  the updated header comment.
- `docker-compose.yml` review — PASS: references `Dockerfile.render`
  explicitly.
- `.dockerignore` review — PASS: both `Dockerfile` and `Dockerfile.render`
  excluded; no stale references to `Dockerfile.vercel` or `vercel.json`.
- README review — PASS: new filenames documented, Render-dashboard note
  present, Vercel section reflects auto-detection (no `vercel.json`).
- Orphan references in `.agent/` historical records (RUN-005, SNAPSHOT-005,
  ADR-006, TASK-004) — these are append-only per protocol Section 1.2
  and must NOT be modified. They remain as historical context.
- Vercel live build verification — PENDING user re-import.

## Commits

### Commit — 9f98580
Date: 2026-09-02
Branch: main

Summary:
fix(vercel): switch from deprecated @vercel/docker builder to native
Dockerfile auto-detection

The @vercel/docker builder was deprecated and removed from Vercel's npm
registry. Vercel now requires a `Dockerfile` in the repo root for
auto-detection (no `vercel.json` needed).

Changes:
- Delete vercel.json (source of the build failure).
- Rename Dockerfile.vercel -> Dockerfile (Vercel auto-detects this name).
- Rename original Render Dockerfile -> Dockerfile.render.
- Update docker-compose.yml to reference Dockerfile.render explicitly.
- Update .dockerignore to exclude both Dockerfile variants from each
  other's build context.
- Update README.md to reflect the new filenames and document the
  Render-dashboard Dockerfile-path setting.
- Update Dockerfile header comments to reference OBS-008 and ADR-007.
- Add .agent/OBSERVATIONS/OBS-008-vercel-docker-builder-deprecated.md.
- Add .agent/DECISIONS/ADR-007-vercel-default-dockerfile.md (supersedes
  ADR-006).
- Add .agent/TASKS/TASK-005-vercel-docker-builder-fix.md (this file).
- Add .agent/RUNS/RUN-2026-09-02-marketplace-006.md + ledger.
- Add .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-006.md.

Verification:
- git mv / git rm operations succeeded.
- Static review of both Dockerfiles: PASS (contents unchanged except
  header comments).
- docker-compose.yml references Dockerfile.render: PASS.
- .dockerignore excludes both variants: PASS.
- README documents new filenames + Render-dashboard note: PASS.
- Vercel live build verification: PENDING user re-import.

Notes:
- Did NOT modify the Vercel-shaped Dockerfile's contents (port
  templating, ephemeral-FS relocation, conditional chown) — only the
  header comment was updated to reference OBS-008 and ADR-007.
- Did NOT modify the Render Dockerfile's contents — only the header
  comment was updated.
- Did NOT modify any Vercel-specific files (vhost.vercel.conf,
  entrypoint.vercel.sh) — they keep their `.vercel` suffix. The naming
  asymmetry (Dockerfile vs. vhost.vercel.conf) is a minor wart
  documented in ADR-007.
- Did NOT modify any Render-specific files (vhost.conf, entrypoint.sh).
- Did NOT modify application code, configuration, migrations, models,
  controllers, Blade templates, or language files.
- Historical .agent/ records (ADR-006, RUN-005, SNAPSHOT-005, TASK-004)
  still reference `Dockerfile.vercel` and `vercel.json` — these are
  append-only per protocol Section 1.2 and remain as historical context.
  ADR-006 is superseded by ADR-007 but is preserved unchanged.

## Remaining Work
- User: re-import the repo into Vercel (or trigger a fresh build on
  the existing Vercel project). The build should now succeed without
  the `@vercel/docker` builder error.
- User: if a Render deployment exists, update its dashboard "Dockerfile
  path" setting from the default `Dockerfile` to `Dockerfile.render`.
  (Local `docker compose up` already works correctly because
  docker-compose.yml references `Dockerfile.render` explicitly.)
- Future: if Vercel re-introduces a `vercel.json` requirement for
  custom build settings, write a new ADR superseding ADR-007.
