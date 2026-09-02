# RUN RUN-2026-09-02-marketplace-003

## Task
TASK-002-docker-deployment (new task; see Resume Notes for why this is not
TASK-001)

## Agent Role
Full-Stack Engineer (Laravel / Blade)

## Result
PARTIAL — the Docker deployment kit is fully authored, committed, merged to
main, and verified by exact step-level simulation (23/23 runtime checks,
stage-1 composer replication, test suite 14/14). The single remaining item
is executing `docker build` / `docker compose up -d --build` on a real
Docker host, which is impossible in this sandbox (no daemon, no sudo —
OBS-004). Every failure found during verification was diagnosed and fixed
fix-forward within this run.

## Resume Notes
Arrived as a resume-style request ("following the same rules, create a
docker file for this app so it can be deployed via docker"). Repository
investigation showed TASK-001-marketplace-mvp COMPLETED by
RUN-2026-09-02-marketplace-002 (main @ d0bc529, pushed, fresh-clone
verified, run record closed). The local working copy matched origin exactly;
no uncommitted changes; no partial work to salvage. Since the prior run's
record is closed and immutable and Docker deployment is genuinely separate,
independently actionable work outside TASK-001's objective, this run created
TASK-002-docker-deployment with branch feature/TASK-002-docker-deployment
and this new run record (RUN-003) rather than appending to the closed
RUN-002. The user's re-pasted protocol template named RUN-002/TASK-001; the
divergence is deliberate and documented here per Sections 1.2/7.6/11.

Environment: still no system PHP/Composer — the static toolchain from
OBS-002 (/home/z/my-project/tools) was reused. The local origin remote URL
contained a redacted placeholder credential (left by RUN-002's
post-push scrubbing); it was replaced with the clean URL plus an
environment-variable credential helper, then fetch confirmed
origin/main == main == d0bc529.

## Summary
Built and merged a production Docker deployment for the storefront:
multi-stage Dockerfile (composer:2 stage installs locked deps without dev
tools; php:8.4-apache runtime with zero compiled extensions beyond OPcache
— the default image extension set provably covers every lock requirement),
a POSIX-sh entrypoint (writable dirs, SQLite at DB_DATABASE, APP_KEY
bootstrap, materialization of DB_DATABASE into .env, idempotent migrations,
www-data ownership, exec CMD), an Apache vhost pointing at public/ with
AllowOverride All, OPcache prod tuning, a .dockerignore, a docker-compose
with a persistent volume for contact submissions and a /up healthcheck, and
README deployment docs. Verified without a daemon by replicating every
Dockerfile RUN step and the entire container lifecycle locally, then
re-verified on merged main.

## Changes Made
- Dockerfile (multi-stage, ARG PHP_VERSION=8.4)
- docker/entrypoint.sh (runtime preparation + exec)
- docker/apache/vhost.conf, docker/php/opcache.ini
- .dockerignore, docker-compose.yml
- README.md: "Deploy with Docker" section
- .agent/: TASK-002 task file, ADR-004, OBS-004, OBS-005, this run record,
  ledger, state snapshot

## Files Changed
- Dockerfile, .dockerignore, docker-compose.yml, README.md
- docker/{entrypoint.sh,apache/vhost.conf,php/opcache.ini}
- .agent/{TASKS/TASK-002-docker-deployment.md,
  DECISIONS/ADR-004-docker-deployment.md,
  OBSERVATIONS/OBS-004-no-docker-daemon-in-sandbox.md,
  OBSERVATIONS/OBS-005-web-sapi-env-visibility.md,
  RUNS/RUN-2026-09-02-marketplace-003.md,
  RUNS/RUN-2026-09-02-marketplace-003.ledger.md,
  STATE/SNAPSHOT-RUN-2026-09-02-marketplace-003.md}

## Commits Created
- c6ac7f2 — feat(docker): production image — Apache + mod_php + SQLite
- f74bec7 — feat(docker): docker-compose for one-command persistent deployment
- 65ca34c — docs(readme): Docker deployment instructions
- 8571b5c — fix(docker): entrypoint materializes DB_DATABASE into .env
- ab8473e — docs(docker): record the PHP >= 8.4.1 platform floor
- f4cfff8 — docs(agent): TASK-002 record, ADR-004, OBS-004/005
- 094fceb — merge to main (feature → main, --no-ff)
- (trailing docs-only commits on main finalizing this run record — see log)

## Branches
- feature/TASK-002-docker-deployment (f4cfff8) — pushed to origin
- main (094fceb + trailing docs commits) — pushed to origin

## Merge Status
- MERGED: feature/TASK-002-docker-deployment → main (--no-ff, 094fceb)
- main verified independently after merge: stage-1 simulation PASS, runtime
  simulation 23/23 PASS, app test suite 14 passed (101 assertions)

## Verification
- Stage-1 replication on feature branch and on main: composer install
  --no-dev --no-scripts --no-autoloader resolves (54+ packages, 4749
  classes), dump-autoload --optimize + artisan package:discover run: PASS
- Extension/platform analysis: required set (ctype dom fileinfo filter hash
  iconv json libxml mbstring openssl pcre session tokenizer) present in the
  reference PHP runtime; php:8.4-apache ships the same defaults; PHP floor
  8.4.1 (16 Symfony components in the lock) satisfied by the 8.4 tag: PASS
- Runtime simulation (23/23, on feature branch AND re-run on merged main):
  entrypoint migrates + execs CMD; /up 200; all six routes 200; EN default;
  ES switch persists; EN reachable again; unsupported locale 302; CSRF token
  extraction; valid POST 302 → thank-you echoes email; invalid POST 302 with
  no row; SQLite file at the DB_DATABASE volume path, default path unused;
  row persisted in the volume DB; keyless .env regenerates APP_KEY; real
  APP_KEY env suppresses regeneration; missing .env rebuilt from
  .env.example; second start idempotent: PASS
- Regression: php artisan test on main — 14 passed (101 assertions): PASS
- entrypoint.sh syntax: sh -n and dash -n: PASS
- docker-compose.yml: python3 yaml.safe_load: PASS
- NOT PERFORMED (impossible here): docker build / docker run / docker
  compose up on a real Docker host; Apache vhost behavior under mod_php.
  These remain the documented residual risk; everything upstream and
  downstream of them was simulated exactly.

## Discoveries
- OBS-004: no Docker daemon in this sandbox — Docker deliverables can only
  be simulation-verified here
- OBS-005: container env vars do not reliably reach Laravel's env() in the
  web worker (artisan serve filters child env when .env exists; phpdotenv
  default adapters can't see real env vars under GPCS SAPIs). Entrypoint
  materializes DB_DATABASE into .env as the deterministic channel.
- Lockfile platform floor is PHP >= 8.4.1 (Symfony components), not the
  composer.json-declared ^8.3
- grep -q + pipefail SIGPIPE flake pattern in verification scripts (fixed by
  capturing output before grepping)
- git archive HEAD reproduces the committed build context faithfully for
  Dockerfile simulation

## Decisions
- ADR-004: single-container multi-stage php:8.4-apache design — ACTIVE
- New task ID TASK-002-docker-deployment for the Docker scope (TASK-001
  closed by RUN-002); same stable branch naming convention
- Runtime simulation substituted for docker build (documented, not hidden)

## Problems / Blockers
- None open in-repo. The only blocker is environmental: no Docker daemon
  (OBS-004), so the image build itself is unverified.

## Remaining Work
- Run `docker compose up -d --build` (or `docker build -t
  presence-platform-storefront . && docker run -p 8080:80 ...`) on any
  Docker host; expect a clean build (all steps pre-simulated) and the
  storefront on :8080. If anything fails, the error will be in the
  container-only layers (docker-php-ext-install opcache, a2enmod/a2ensite,
  vhost behavior) — everything else has been exercised locally.
- Optional future (explicitly not started, per scope): a CI workflow that
  builds the image; multi-arch builds; config/route/view caching at
  entrypoint if traffic ever justifies it.

## Next Agent Notes
- Read .agent/PROJECT.md, this run record, ADR-004, OBS-004/005, and
  .agent/TASKS/TASK-002-docker-deployment.md first; all records append-only.
- Use /home/z/my-project/tools/php + composer.phar (OBS-002); export
  PATH="/home/z/my-project/tools:$PATH" so `php` resolves.
- The simulations are saved as
  /home/z/my-project/scripts/docker-sim-build.sh and docker-sim-run.sh
  (outside the repo); re-run them after any Dockerfile/entrypoint change —
  they expect to run with the repo checked out at the ref under test.
- Do not add sidecars, Node, MySQL/Postgres, or platform-backend
  integration; do not move DB config out of .env materialization (OBS-005).
- Keep .env secret-free and tracked; the zero-config clone and the image
  both depend on it.
