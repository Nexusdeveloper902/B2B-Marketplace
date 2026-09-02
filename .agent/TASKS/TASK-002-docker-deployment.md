# TASK-002-docker-deployment

## Objective

Make the Presence Platform marketplace storefront deployable via Docker:
a production image (`Dockerfile`) plus a one-command persistent deployment
(`docker-compose.yml`), without violating any TASK-001 constraint (SQLite
only, no Node/Vite, no auth/payments/multi-vendor mechanics, no
platform-backend integration, bilingual EN/ES preserved).

Task created by RUN-2026-09-02-marketplace-003. TASK-001 was COMPLETED and
closed by RUN-2026-09-02-marketplace-002; Docker deployment is genuinely
separate, independently actionable work outside that task's objective, so
it receives its own task record (a new run never creates a task — but new
scope outside a completed task's objective does).

## Requirements

- `Dockerfile`: production image — Apache + mod_php + SQLite, no Node, no
  external services, self-hosted fonts, no compiled extensions beyond OPcache
- Entrypoint: writable dirs, SQLite file at `DB_DATABASE`, APP_KEY bootstrap
  for a keyless/missing `.env`, idempotent migrations, ownership for the web
  user, then exec the container command
- `.dockerignore`: keep `.git`, `.agent`, tests, host vendor, runtime state,
  and the Docker build files themselves out of the image
- `docker-compose.yml`: one command, persistent volume for contact
  submissions, healthcheck against `/up`, port override via `APP_PORT`
- README: deployment instructions (build/run/compose)
- Verification level achievable here: full local simulation of every
  Dockerfile build and runtime step (no Docker daemon exists in this
  environment — OBS-004). The image build itself must be confirmed on a
  Docker host by whoever deploys.

## Acceptance Criteria

- [x] Docker kit committed (Dockerfile, .dockerignore, docker/entrypoint.sh,
      docker/apache/vhost.conf, docker/php/opcache.ini)
- [x] docker-compose.yml + README deployment docs committed
- [x] Stage-1 replication: `composer install --no-dev` resolves cleanly;
      `dump-autoload --optimize` fires `artisan package:discover` (PASS)
- [x] Platform/extension analysis: required extensions (ctype dom fileinfo
      filter hash iconv json libxml mbstring openssl pcre session tokenizer)
      are within php:8.4-apache defaults; PHP floor >= 8.4.1 satisfied by
      the 8.4 tag
- [x] Runtime simulation 23/23: entrypoint; all six routes 200; EN default,
      ES persists, EN reachable again, unsupported locale ignored; CSRF +
      valid POST → 302 → thank-you with email echo; invalid POST rejected
      with no row; row persisted at the DB_DATABASE volume path (default
      path unused); APP_KEY bootstrap branches; idempotent restart
- [x] No regression: app test suite 14 passed (101 assertions)
- [x] No TASK-001 constraint violated (no new runtime deps, no Node, no
      auth/payments/multi-vendor, no platform integration, bilingual intact)
- [ ] `docker compose up -d --build` on a real Docker host — NOT VERIFIABLE
      in this sandbox (no daemon, no sudo; OBS-004). All build and runtime
      steps were simulated instead; this is the only remaining item.

## Commits

## Commit — c6ac7f2

Date: 2026-09-02
Branch: feature/TASK-002-docker-deployment

Summary:
Production image kit: multi-stage Dockerfile, entrypoint, Apache vhost,
OPcache config, .dockerignore.

Changes:
- Dockerfile: stage 1 (composer:2) installs locked deps without dev tools
  and dumps the optimized autoloader; stage 2 (php:8.4-apache) applies
  php.ini-production, OPcache, mod_rewrite, storefront vhost, app copy
- docker/entrypoint.sh: runtime preparation + exec
- docker/apache/vhost.conf: DocumentRoot public/, AllowOverride All
- docker/php/opcache.ini: prod tuning (validate_timestamps=0)
- .dockerignore

Verification:
- sh -n / dash -n on entrypoint: PASS
- context contents (tests/.agent/vendor excluded): PASS

## Commit — f74bec7

Date: 2026-09-02
Branch: feature/TASK-002-docker-deployment

Summary:
docker-compose.yml for one-command persistent deployment.

Changes:
- Named volume storefront-data + DB_DATABASE env, healthcheck via PHP CLI
  against /up, APP_PORT override, restart policy

Verification:
- python3 yaml.safe_load: PASS

## Commit — 65ca34c

Date: 2026-09-02
Branch: feature/TASK-002-docker-deployment

Summary:
README "Deploy with Docker" section (build/run/compose paths).

Verification:
- Manual review; matches implemented behavior

## Commit — 8571b5c

Date: 2026-09-02
Branch: feature/TASK-002-docker-deployment

Summary:
Entrypoint materializes a provided DB_DATABASE into .env (fix found by
simulation).

Changes:
- docker/entrypoint.sh step 3b (sed-replace or append), compose comment and
  README wording corrected to verified behavior

Verification:
- docker-sim-run: 22/23 -> after sim-script fix, 23/23 PASS (contact POST
  persisted to the volume DB)
- Root cause and evidence recorded in OBS-005

## Commit — ab8473e

Date: 2026-09-02
Branch: feature/TASK-002-docker-deployment

Summary:
Dockerfile comment documenting the PHP >= 8.4.1 platform floor from the
lockfile (16 Symfony components).

Verification:
- composer.lock analysis (documented in ADR-004)

## Merge — 094fceb

Date: 2026-09-02
Source Branch: feature/TASK-002-docker-deployment (f4cfff8)
Target Branch: main

Result:
MERGED (--no-ff; 11 files, 564 insertions)

Verification on main (independent, re-run after merge):
- Stage-1 simulation (composer install --no-dev, dump-autoload +
  package:discover): PASS
- Runtime simulation (entrypoint, all routes, EN/ES, contact form,
  volume persistence, APP_KEY branches, idempotent restart): 23/23 PASS
- php artisan test: 14 passed (101 assertions)
- Remaining (environmental, not in-repo): docker build / compose up on a
  real Docker host (OBS-004)

Result: PARTIAL — everything authorable and simulation-verifiable is done
and merged; image build execution pending a Docker host.
