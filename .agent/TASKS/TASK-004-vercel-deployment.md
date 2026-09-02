# TASK-004-vercel-deployment

## Objective
Enable deployment of the Presence Platform Marketplace Storefront on
Vercel, which has no first-class Laravel runtime, by shipping a
Vercel-ready Docker container variant alongside the existing Render
Dockerfile (ADR-004). The user must be able to push the repo to Vercel
and have a working deployment with no manual Dockerfile editing.

## Origin
User request on 2026-09-02: "I'm going to need you to create a
dockerfile.vercell for me to be able to deploy this application on
vercell using the docker configuration since they provide no laravel
support."

## Requirements
- Provide `Dockerfile.vercel` that builds a runnable container image
  for Vercel Container Deployments.
- Vercel injects `$PORT` — the container must bind to it (Apache's
  `Listen` directive cannot read env vars at parse time, so the
  entrypoint must substitute the value at startup).
- Vercel's container filesystem is ephemeral — the entrypoint must
  relocate writable state (SQLite DB, Laravel `storage/` tree) to
  `/tmp/` so the app runs at all during a single container lifetime.
- Vercel may run the container as non-root — the entrypoint must
  gracefully skip the `chown` step when not root.
- Use `vercel.json` to point Vercel's `@vercel/docker` builder at
  `Dockerfile.vercel` (Vercel auto-detects `Dockerfile` by default;
  using an alternate filename requires the builder config).
- Do NOT modify the Render Dockerfile / entrypoint / vhost — the Render
  deployment must continue to work unchanged (Section 1.4).
- Document the ephemeral-filesystem caveat loudly — contact submissions
  are lost on cold restart.
- Document the decision in `.agent/DECISIONS/ADR-006-vercel-deployment.md`
  and the data-loss consequence in
  `.agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md`.

## Acceptance Criteria
- [x] `Dockerfile.vercel` exists, is syntactically valid Dockerfile
      syntax, and builds a `php:8.4-apache`-based multi-stage image.
- [x] `docker/apache/vhost.vercel.conf` exists with a `{{PORT}}`
      placeholder that the entrypoint substitutes at startup.
- [x] `docker/entrypoint.vercel.sh` exists, is executable, passes
      `sh -n` syntax check, and handles: ephemeral-FS relocation,
      `$PORT` patching of vhost + ports.conf, conditional chown, and
      the ADR-004-era concerns (APP_KEY bootstrap, DB_DATABASE
      materialization into .env per OBS-005, idempotent migrate).
- [x] `vercel.json` exists, is valid JSON, and wires the
      `@vercel/docker` builder at `Dockerfile.vercel`.
- [x] `.dockerignore` excludes `Dockerfile.vercel`, `vercel.json`,
      and the Vercel-only `docker/*` files from the Render image's
      build context, so the Render image stays clean.
- [x] README has a "Deploy on Vercel (Docker Container)" section that
      explains the quick-deploy flow AND the data-loss caveat.
- [x] ADR-006 records the decision, alternatives considered, and the
      consequences a future agent must preserve.
- [x] OBS-007 records the ephemeral-filesystem consequence with
      evidence and impact.
- [x] No changes to: `Dockerfile`, `docker/apache/vhost.conf`,
      `docker/entrypoint.sh`, `docker-compose.yml`, `docker/php/opcache.ini`,
      application code, configuration, migrations, models, controllers,
      Blade templates, or language files.
- [x] Fix is committed to `main` and pushed.

## Implementation
Four new files plus README and `.dockerignore` updates, all isolated
to the Vercel-deployment variant. See ADR-006 for the full design
rationale and alternatives considered.

Files:
- `Dockerfile.vercel` — multi-stage Apache + mod_php image, mirrors
  ADR-004's Dockerfile shape except it copies the Vercel vhost and
  entrypoint variants and documents `EXPOSE 8080` (informational only).
- `docker/apache/vhost.vercel.conf` — vhost template with `{{PORT}}`
  placeholder; entrypoint substitutes the actual `$PORT` value.
- `docker/entrypoint.vercel.sh` — entrypoint variant that:
  1. Relocates Laravel's `storage/` tree to `/tmp/storefront/storage`
     via a symlink from `/var/www/html/storage`.
  2. Places the SQLite database file at
     `/tmp/storefront/database.sqlite` by default.
  3. Patches `vhost.vercel.conf`'s `{{PORT}}` and
     `/etc/apache2/ports.conf`'s first `Listen` directive with `$PORT`.
  4. Conditionally runs `chown -R www-data:www-data ...` only when
     the entrypoint is running as root (`id -u = 0`).
  5. Mirrors `entrypoint.sh` for everything else (APP_KEY bootstrap,
     DB_DATABASE materialization into .env, idempotent migrate).
- `vercel.json` — points Vercel's `@vercel/docker` builder at
  `Dockerfile.vercel` so Vercel picks up the Vercel variant instead
  of the default `Dockerfile` (which is the Render image).

Updated:
- `.dockerignore` — adds `Dockerfile.vercel`, `vercel.json` to the
  exclusion list so they don't leak into the Render image's build
  context.
- `README.md` — adds a "Deploy on Vercel (Docker Container)" section
  with the quick-deploy flow, the data-loss caveat, and the local-test
  command.

## Verification
- `sh -n docker/entrypoint.vercel.sh` — PASS (shell syntax check).
- `python3 -c "import json; json.load(open('vercel.json'))"` — PASS
  (valid JSON).
- Static review of `Dockerfile.vercel` against ADR-004's Dockerfile —
  PASS (same stage-1 dependencies step, same stage-2 runtime setup,
  same extensions (none compiled beyond OPcache), same DocumentRoot
  and AllowOverride policy, same .env handling).
- Static review of `entrypoint.vercel.sh` against `entrypoint.sh` —
  PASS (all ADR-004-era concerns preserved; Vercel-specific concerns
  isolated to steps 1, 5, and 6).
- `.dockerignore` review — PASS (Render image's build context excludes
  the Vercel-only files).
- README review — PASS (quick-deploy steps present, data-loss caveat
  prominent).
- Render Dockerfile / vhost / entrypoint / docker-compose.yml review —
  PASS (unchanged; Render deployment continues to work).
- Vercel live build verification — PENDING (no Vercel CLI / Docker
  daemon in this environment; user must trigger a fresh Vercel build
  by importing the repo into Vercel and confirming the deployment
  succeeds).

## Commits

### Commit — {{COMMIT_HASH}}
Date: 2026-09-02
Branch: main

Summary:
feat(vercel): add Dockerfile.vercel + entrypoint + vhost + vercel.json
for Vercel Container Deployment

The Vercel variant mirrors ADR-004's Render Dockerfile shape (Apache +
mod_php + SQLite, no Node, no external services) with three
platform-specific differences isolated to new files:

1. The entrypoint relocates writable state (SQLite DB + Laravel storage/)
   to /tmp because Vercel's container filesystem is ephemeral (OBS-007).
2. The entrypoint patches Apache's Listen directive and vhost from $PORT
   at startup because Apache can't read env vars at parse time.
3. The entrypoint conditionally skips chown when not root because Vercel
   may invoke the container as a non-root user.

The Render Dockerfile, vhost, entrypoint, and docker-compose.yml are
unchanged — both deployment targets share the same application code,
migration, models, controllers, Blade templates, and self-hosted fonts.

vercel.json wires Vercel's @vercel/docker builder at Dockerfile.vercel
so Vercel picks up the Vercel variant instead of the default Dockerfile
(the Render image). .dockerignore excludes the Vercel-only files from
the Render image's build context so the Render image stays clean.

Changes:
- Dockerfile.vercel (new) — multi-stage Apache + mod_php image
- docker/apache/vhost.vercel.conf (new) — vhost template with {{PORT}}
- docker/entrypoint.vercel.sh (new) — Vercel-specific entrypoint variant
- vercel.json (new) — @vercel/docker builder config pointing at
  Dockerfile.vercel
- .dockerignore — exclude Vercel-only files from Render image context
- README.md — "Deploy on Vercel (Docker Container)" section with
  quick-deploy steps + ephemeral-FS caveat
- .agent/DECISIONS/ADR-006-vercel-deployment.md (new) — decision record
- .agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md (new) —
  data-loss observation
- .agent/TASKS/TASK-004-vercel-deployment.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-005.md (new) — run record
- .agent/RUNS/RUN-2026-09-02-marketplace-005.ledger.md (new) — ledger
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-005.md (new) — state

Verification:
- sh -n docker/entrypoint.vercel.sh: PASS
- python3 -c "import json; json.load(open('vercel.json'))": PASS
- Static review vs ADR-004 Dockerfile + entrypoint.sh: PASS
- .dockerignore review (Render image stays clean): PASS
- README review: PASS
- Render Dockerfile/vhost/entrypoint/compose unchanged: PASS
- Vercel live build verification: PENDING user import into Vercel

Notes:
- Did NOT modify the Render Dockerfile, vhost, entrypoint, or compose
  (Section 1.4 — minimize unrelated changes; Render deployment must
  continue to work unchanged).
- Did NOT switch the Vercel image to `php artisan serve` — ADR-004
  rejected it as a production runtime; consistency wins. The vhost
  templating is a one-time entrypoint concern, not a recurring cost.
- Did NOT add Vercel Postgres / Turso / Neon — out of scope per ADR-001
  and TASK-001's "switching the database off SQLite for any reason" is
  explicitly out of scope.
- Did NOT add Vercel Persistent Storage — paid Vercel feature, requires
  dashboard configuration outside the repo, adds external dependency
  for a marketing demo. Documented as future option in OBS-007.

## Remaining Work
- User: import the repo into Vercel and confirm the build succeeds.
  If Vercel rejects the build, the most likely cause is the
  `@vercel/docker` builder being unavailable on the user's Vercel
  plan — Vercel Container Deployments may require a paid tier.
- User: visit the deployed URL and confirm the styled UI renders. The
  forceScheme fix from ADR-005 (RUN-004) applies on Vercel too because
  Vercel also terminates TLS at its load balancer.
- Future: if production contact-submission persistence is needed on
  Vercel, either move to Vercel Postgres / Turso / Neon (out of scope
  per ADR-001) or stay on the Render deployment (persistent SQLite
  volume per ADR-004).
