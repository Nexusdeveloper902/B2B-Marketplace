# Activity Ledger — RUN-2026-09-02-marketplace-005

## Resume investigation
- ACTION: re-establish repo state from prior runs
- COMMAND: git status && git log --oneline -5 && git pull --ff-only origin main
- RESULT: clean main at 0407c3e; phantom filemode changes silenced via
  `git config core.fileMode false` (filesystem preserves exec bit
  differently than original clone source; no content changes).

## Recover prior task documentation
- FILES:
  - .agent/PROJECT.md
  - .agent/TASKS/TASK-001-marketplace-mvp.md (completed in RUN-002)
  - .agent/TASKS/TASK-002-docker-deployment.md (completed in RUN-003)
  - .agent/TASKS/TASK-003-render-styling-fix.md (completed in RUN-004)
  - .agent/DECISIONS/ADR-001..005
  - .agent/OBSERVATIONS/OBS-001..006
  - .agent/RUNS/RUN-2026-09-02-marketplace-002..004.md
  - .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-002..004.md
- RESULT: FOUND — full .agent/ tree populated by prior runs. ADR-004
  (Render Dockerfile) and ADR-005 (forceScheme fix) directly informed
  this run's design (parallel Vercel variant that mirrors ADR-004's
  shape and reuses ADR-005's TLS-termination insight for Vercel).

## Establish current truth
- INSPECTION:
  - Dockerfile — multi-stage Apache + mod_php + SQLite for Render,
    DocumentRoot=/var/www/html/public, mod_rewrite, EXPOSE 80.
  - docker/apache/vhost.conf — `<VirtualHost *:80>`, AllowOverride All,
    passes static files via RewriteCond %{REQUEST_FILENAME} !-f.
  - docker/entrypoint.sh — recreates writable dirs, SQLite file, APP_KEY
    bootstrap, DB_DATABASE materialization into .env (per OBS-005),
    migrate --force, chown when root.
  - .dockerignore — excludes .git, .agent, tests, vendor/, runtime state,
    Dockerfile, docker-compose.yml.
  - .env / .env.example — APP_ENV=production, APP_URL=http://localhost:8000,
    DB_CONNECTION=sqlite, secret-free (per ADR-004).
  - public/css/app.css, public/css/fonts.css, public/fonts/*.woff2 —
    all present and tracked (the styling fix from RUN-004 makes these
    load correctly behind Vercel's TLS-terminating proxy too, since
    Vercel also forwards X-Forwarded-Proto: https).
- DIAGNOSIS: Vercel needs a parallel deployment variant because of
  three platform-specific constraints (port injection, ephemeral FS,
  possible non-root execution) that the existing Render Dockerfile does
  not satisfy. Recorded in ADR-006.

## Design Vercel variant
- FILES DESIGNED:
  - Dockerfile.vercel — multi-stage Apache + mod_php image, mirrors
    ADR-004 Dockerfile shape with Vercel vhost + entrypoint variants.
  - docker/apache/vhost.vercel.conf — vhost template with {{PORT}}
    placeholder.
  - docker/entrypoint.vercel.sh — Vercel-specific entrypoint:
    relocate storage/ + SQLite to /tmp (ephemeral FS), patch Apache's
    Listen + vhost from $PORT (Apache can't read env vars at parse),
    conditional chown when not root, mirror ADR-004-era concerns.
  - vercel.json — @vercel/docker builder pointing at Dockerfile.vercel.
- DESIGN RECORDED IN: ADR-006.

## Apply changes
- FILES WRITTEN:
  - Dockerfile.vercel
  - docker/apache/vhost.vercel.conf
  - docker/entrypoint.vercel.sh (chmod +x via Write tool)
  - vercel.json
- FILES UPDATED:
  - .dockerignore — exclude Dockerfile.vercel + vercel.json from
    Render image context.
  - README.md — "Deploy on Vercel (Docker Container)" section.

## Verify
- ACTION: shell syntax check on entrypoint
- COMMAND: sh -n docker/entrypoint.vercel.sh
- RESULT: PASS (SYNTAX OK)
- ACTION: JSON validation on vercel.json
- COMMAND: python3 -c "import json; json.load(open('vercel.json'))"
- RESULT: PASS (JSON OK)
- ACTION: static review of Dockerfile.vercel vs ADR-004 Dockerfile
- RESULT: PASS — same stage-1 deps step, same stage-2 runtime setup,
  same extensions (none compiled beyond OPcache), same DocumentRoot +
  AllowOverride policy, same .env handling. Only differences: copies
  vhost.vercel.conf and entrypoint.vercel.sh, EXPOSE 8080 instead of 80.
- ACTION: static review of entrypoint.vercel.sh vs entrypoint.sh
- RESULT: PASS — all ADR-004-era concerns preserved (APP_KEY bootstrap,
  DB_DATABASE materialization per OBS-005, idempotent migrate). Vercel-
  specific concerns isolated to: step 1 (ephemeral-FS relocation to
  /tmp via symlink), step 5 ($PORT patching of vhost + ports.conf),
  step 6 (conditional chown when id -u = 0).
- ACTION: verify Render files unchanged
- COMMAND: git diff --stat Dockerfile docker/apache/vhost.conf docker/entrypoint.sh docker-compose.yml docker/php/opcache.ini
- RESULT: PASS — no changes to Render deployment files.
- ACTION: Vercel live build verification
- RESULT: PENDING — no Vercel CLI or Docker daemon in this environment.

## Document
- FILES:
  - .agent/DECISIONS/ADR-006-vercel-deployment.md
  - .agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md
  - .agent/TASKS/TASK-004-vercel-deployment.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-005.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-005.ledger.md (this file)
  - .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-005.md
- RESULT: SUCCESS — all append-only records created per protocol
  Sections 11-17. No historical records modified or overwritten.

## Commit
- ACTION: commit Vercel variant + .agent/ docs to main
- BRANCH: main
- MESSAGE: feat(vercel): add Dockerfile.vercel + entrypoint + vhost +
  vercel.json for Vercel Container Deployment
- RESULT: (hash recorded in TASK-004 commit entry after git commit)

## Push
- ACTION: push main to origin
- COMMAND: git push origin main
- RESULT: (recorded after push)
