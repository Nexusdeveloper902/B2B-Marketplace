# Activity Ledger — RUN-2026-09-02-marketplace-006

## Resume investigation
- ACTION: re-establish repo state from prior runs
- COMMAND: git config core.fileMode false && git pull --ff-only origin main
- RESULT: clean main at 3fce661.

## Receive user-reported Vercel build failure
- USER REPORT on 2026-09-02:
  ```
  > Installing Builder: @vercel/docker
  Error: The package `@vercel/docker` is not published on the npm registry
  ```
  Vercel also warned that `builds` in vercel.json overrides Project
  Settings.
- DIAGNOSIS: Vercel's `@vercel/docker` builder has been deprecated and
  removed from the npm registry. RUN-005's `vercel.json` (which
  declared `builds: [{ src: "Dockerfile.vercel", use: "@vercel/docker" }]`)
  is the source of the failure. Recorded in OBS-008.

## Recover prior task documentation
- FILES:
  - .agent/TASKS/TASK-004-vercel-deployment.md (completed in RUN-005)
  - .agent/DECISIONS/ADR-006-vercel-deployment.md (the @vercel/docker
    approach — now superseded)
  - .agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md (still
    valid; the Vercel ephemeral-FS consequence is independent of the
    builder mechanism)
  - .agent/RUNS/RUN-2026-09-02-marketplace-005.md
- RESULT: FOUND — full .agent/ tree populated by RUN-005. ADR-006's
  approach is no longer viable; ADR-007 supersedes it.

## Plan fix
- DECISION: switch to Vercel's native Dockerfile auto-detection by
  file rename. No `vercel.json` needed.
- FILES AFFECTED:
  - Delete `vercel.json`.
  - Rename `Dockerfile.vercel` → `Dockerfile` (Vercel auto-detects).
  - Rename original Render `Dockerfile` → `Dockerfile.render`.
  - Update `docker-compose.yml` to reference `Dockerfile.render`.
  - Update `.dockerignore` to exclude both variants.
  - Update `README.md` with new filenames + Render-dashboard note.
- DESIGN RECORDED IN: ADR-007.

## Apply changes
- COMMAND: git mv Dockerfile Dockerfile.render && git mv Dockerfile.vercel Dockerfile && git rm vercel.json
- RESULT: PASS — verified via `git status --short`:
  ```
  M  .dockerignore
  MM Dockerfile
  A  Dockerfile.render
  D  Dockerfile.vercel
  M  README.md
  M  docker-compose.yml
  D  vercel.json
  ```
  (The `MM` on Dockerfile was from header-comment edits applied after
  the rename; `git add -A` resolved it.)

## Verify
- ACTION: confirm orphan references in active files
- COMMAND: grep -rn "Dockerfile.vercel\|vercel.json\|@vercel/docker" .
- RESULT: PASS — no orphan references in active files (Dockerfile,
  Dockerfile.render, README.md, docker-compose.yml, .dockerignore).
  Historical references in `.agent/RUNS/RUN-005.md`,
  `.agent/STATE/SNAPSHOT-005.md`, `.agent/DECISIONS/ADR-006.md`, and
  `.agent/TASKS/TASK-004.md` are append-only per protocol Section 1.2
  and remain as historical context.
- ACTION: static review of Dockerfile (Vercel variant) vs pre-rename
  Dockerfile.vercel
- RESULT: PASS — same multi-stage shape, same stage-1 deps step, same
  stage-2 runtime setup, same Vercel-specific vhost and entrypoint
  references. Only the header comment changed.
- ACTION: static review of Dockerfile.render vs pre-rename Dockerfile
- RESULT: PASS — identical except for the header comment.
- ACTION: docker-compose.yml review
- RESULT: PASS — references `Dockerfile.render` explicitly.
- ACTION: .dockerignore review
- RESULT: PASS — both `Dockerfile` and `Dockerfile.render` excluded;
  no stale references to `Dockerfile.vercel` or `vercel.json`.
- ACTION: README review
- RESULT: PASS — new filenames documented, Render-dashboard note
  present, Vercel section reflects auto-detection (no `vercel.json`).
- ACTION: Vercel live build verification
- RESULT: PENDING — no Vercel CLI in this environment.

## Document
- FILES:
  - .agent/OBSERVATIONS/OBS-008-vercel-docker-builder-deprecated.md
  - .agent/DECISIONS/ADR-007-vercel-default-dockerfile.md (supersedes
    ADR-006)
  - .agent/TASKS/TASK-005-vercel-docker-builder-fix.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-006.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-006.ledger.md (this file)
  - .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-006.md
- RESULT: SUCCESS — all append-only records created per protocol
  Sections 11-17. No historical records modified or overwritten.
  ADR-006 is superseded by ADR-007 but is preserved unchanged.

## Commit
- ACTION: commit fix + .agent/ docs to main
- BRANCH: main
- MESSAGE: fix(vercel): switch from deprecated @vercel/docker builder
  to native Dockerfile auto-detection
- RESULT: (hash recorded in TASK-005 commit entry after git commit)

## Push
- ACTION: push main to origin
- COMMAND: git push origin main
- RESULT: (recorded after push)
