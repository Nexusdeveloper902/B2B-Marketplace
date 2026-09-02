# STATE SNAPSHOT — RUN-2026-09-02-marketplace-003

## Overall Status
TASK-002-docker-deployment PARTIAL (delivered and merged; the docker image
build itself remains to be executed on a Docker host — OBS-004). TASK-001
remains COMPLETED from RUN-2026-09-02-marketplace-002.

## Completed
- Production Docker image kit: multi-stage Dockerfile (composer:2 stage →
  php:8.4-apache runtime, zero compiled extensions beyond OPcache),
  entrypoint, Apache vhost, OPcache config, .dockerignore
- docker-compose.yml: one-command deployment, persistent `storefront-data`
  volume for contact submissions, /up healthcheck, APP_PORT override
- README "Deploy with Docker" section
- Full step-level verification of build and runtime by local simulation
  (23/23 runtime checks; stage-1 composer replication; 14/14 app tests) —
  on the feature branch AND on merged main
- All .agent records for TASK-002 / RUN-003 (task file, ADR-004, OBS-004,
  OBS-005, run record, ledger, this snapshot)

## In Progress
- Nothing

## Blocked
- `docker build` / `docker compose up` execution: no Docker daemon and no
  sudo in this environment (OBS-004). Not blocked by anything in-repo.

## Known Problems
- None in-repo. Residual (container-only) risk, documented in ADR-004 and
  the run record: the layers that only a real Docker host can exercise —
  docker-php-ext-install opcache, a2enmod/a2ensite wiring, and the Apache
  vhost behavior under mod_php. All upstream (dependency install, package
  discovery) and downstream (entrypoint, migrations, routes, form, EN/ES)
  steps were simulated exactly and pass.

## Important Current Facts
- Marketplace app uses SQLite (DB via .env DB_DATABASE; compose places the
  file on the storefront-data volume)
- Marketplace app has NO dependency on the core Presence Platform backend
- Design direction "Event Ledger" (ADR-002) and bilingual EN/ES (ADR-003)
  are untouched by this run
- Docker direction (ADR-004): single container, multi-stage, php:8.4-apache,
  mod_php + .htaccess routing, entrypoint materializes DB_DATABASE into
  .env because container env vars don't reliably reach web workers
  (OBS-005)
- Lockfile platform floor: PHP >= 8.4.1 (16 Symfony components)
- .env stays tracked and secret-free — both zero-config clones and the
  image depend on it
- Environment: static PHP 8.4.23 + Composer 2.10.3 in /home/z/my-project/tools
  (OBS-002); no Docker daemon (OBS-004)
- Simulation scripts live OUTSIDE the repo:
  /home/z/my-project/scripts/docker-sim-build.sh, docker-sim-run.sh,
  sim-db.php

## Current Main Commit
094fceb = merge commit (all TASK-002 code + docs through f4cfff8; verified
from merged main). Trailing docs-only commits from this run's report
finalization follow on top (see git log); they contain no application
changes.

## Current Main Status
BUILDABLE — verified on merged main: stage-1 simulation PASS, runtime
simulation 23/23 PASS, php artisan test 14 passed (101 assertions).
(First real `docker build` still pending a Docker host.)

## Active Branches
- main @ 094fceb (+ trailing docs commits; pushed)
- feature/TASK-002-docker-deployment @ f4cfff8 (pushed; fully merged)
- feature/TASK-001-marketplace-mvp @ a3c54bf (pushed; merged by RUN-002)
