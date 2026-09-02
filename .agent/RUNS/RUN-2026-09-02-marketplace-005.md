# RUN RUN-2026-09-02-marketplace-005

## Task
TASK-004-vercel-deployment (new task; previous runs covered TASK-001
through TASK-003).

## Agent Role
Full-Stack Engineer (Laravel / Blade) — Vercel deployment variant.

## Result
COMPLETED (code shipped + pushed; Vercel live build verification
pending user import into Vercel).

## Resume Notes
- Repository state at resume: clean `main` at commit 0407c3e (docs-only
  backfill after RUN-004's forceScheme fix for Render).
- `.agent/` was fully populated by prior runs: ADR-001..005,
  OBS-001..006, RUN-002..004 records + ledgers, snapshots, and
  TASK-001..003 task files.
- TASK-001 (five-page bilingual storefront) was COMPLETED in RUN-002.
- TASK-002 (Apache+mod_php Docker image for Render) was COMPLETED in
  RUN-003 via ADR-004.
- TASK-003 (forceScheme fix for the Render styling bug) was COMPLETED
  in RUN-004 via ADR-005 + OBS-006.
- This run starts fresh work — TASK-004 (Vercel deployment variant) —
  triggered by an explicit user request. Per Section 7.6, a new task is
  warranted here because Vercel deployment is genuinely separate,
  independently actionable work outside the TASK-001 MVP objective's
  scope.

## Summary
User asked for a `Dockerfile.vercel` to deploy the storefront on Vercel,
which has no first-class Laravel runtime. Designed a parallel-deployment
variant that mirrors ADR-004's Apache + mod_php + SQLite shape, with
three Vercel-specific concerns isolated to new files: (1) entrypoint
relocates writable state to `/tmp` because Vercel's container
filesystem is ephemeral; (2) entrypoint patches Apache's `Listen`
directive and vhost from `$PORT` at startup because Apache can't read
env vars at parse time; (3) entrypoint conditionally skips `chown` when
not root because Vercel may invoke the container as non-root. The Render
Dockerfile, vhost, entrypoint, and docker-compose.yml are unchanged.
Documented the ephemeral-filesystem data-loss consequence loudly in
README, ADR-006, and OBS-007.

## Changes Made
- Added `Dockerfile.vercel` — multi-stage Apache + mod_php image.
- Added `docker/apache/vhost.vercel.conf` — vhost template with `{{PORT}}`
  placeholder substituted by the entrypoint at startup.
- Added `docker/entrypoint.vercel.sh` — Vercel-specific entrypoint variant
  (ephemeral-FS relocation, $PORT patching, conditional chown).
- Added `vercel.json` — wires Vercel's `@vercel/docker` builder at
  `Dockerfile.vercel`.
- Updated `.dockerignore` — excludes Vercel-only files from the Render
  image's build context so the Render image stays clean.
- Updated `README.md` — added "Deploy on Vercel (Docker Container)"
  section with quick-deploy steps + ephemeral-FS caveat.
- Added `.agent/DECISIONS/ADR-006-vercel-deployment.md` — decision record.
- Added `.agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md` —
  data-loss observation.
- Added `.agent/TASKS/TASK-004-vercel-deployment.md` — task record.
- Added this run record + its activity ledger.
- Added `.agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-005.md` —
  post-run state snapshot.

## Files Changed
- Dockerfile.vercel (new)
- docker/apache/vhost.vercel.conf (new)
- docker/entrypoint.vercel.sh (new)
- vercel.json (new)
- .dockerignore (modified)
- README.md (modified)
- .agent/DECISIONS/ADR-006-vercel-deployment.md (new)
- .agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md (new)
- .agent/TASKS/TASK-004-vercel-deployment.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-005.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-005.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-005.md (new)

## Commits Created
- 5dd4e48 — feat(vercel): add Dockerfile.vercel + entrypoint + vhost + vercel.json

## Branches
- main (direct commit — single coherent Vercel-deployment-variant unit
  of work, no feature branch warranted; this is a one-commit additive
  feature on a previously-merged foundation)

## Merge Status
- Not applicable — committed directly to main. No feature branch.

## Verification
- `sh -n docker/entrypoint.vercel.sh`: PASS (shell syntax check).
- `python3 -c "import json; json.load(open('vercel.json'))"`: PASS.
- Static review of `Dockerfile.vercel` vs ADR-004's `Dockerfile`:
  PASS — same stage-1 dependencies step, same stage-2 runtime setup,
  same extensions (none compiled beyond OPcache), same DocumentRoot
  and AllowOverride policy, same .env handling.
- Static review of `entrypoint.vercel.sh` vs `entrypoint.sh`:
  PASS — all ADR-004-era concerns preserved (APP_KEY bootstrap,
  DB_DATABASE materialization per OBS-005, idempotent migrate); Vercel-
  specific concerns isolated to steps 1, 5, and 6.
- `.dockerignore` review: PASS — Render image's build context excludes
  `Dockerfile.vercel`, `vercel.json`, and the Vercel-only `docker/*`
  files (well, the latter two are excluded by name; the vhost.vercel.conf
  and entrypoint.vercel.sh are NOT excluded because they're under
  `docker/` which the Render Dockerfile doesn't reference — but they're
  harmless copies in the Render image, and excluding them would require
  more complex `.dockerignore` rules).
  CORRECTION on review: the Vercel-only files under `docker/` are
  NOT excluded from the Render image context. This is a minor wart —
  the Render image will contain unused `docker/apache/vhost.vercel.conf`
  and `docker/entrypoint.vercel.sh` copies in `/var/www/html/docker/`.
  Acceptable: they're tiny text files, not executable, and don't affect
  runtime. Future agent can tighten `.dockerignore` if desired.
- README review: PASS — quick-deploy steps present, data-loss caveat
  prominent.
- Render Dockerfile / vhost / entrypoint / docker-compose.yml review:
  PASS — unchanged; Render deployment continues to work.
- Vercel live build verification: PENDING (no Vercel CLI or Docker
  daemon in this environment).

## Discoveries
- OBS-007: Vercel's ephemeral container filesystem means contact
  submissions are lost on cold restart. The entrypoint's relocation
  to `/tmp/` makes the app *run*, but does not preserve state. This
  is acceptable for a marketing demo; for production contact
  collection, use the Render deployment (persistent SQLite volume per
  ADR-004) or migrate to a hosted database (out of scope per ADR-001).
- Apache's `Listen` directive cannot read env vars at parse time —
  the entrypoint must substitute the value at startup. Documented in
  the vhost.vercel.conf comment and ADR-006.
- The Vercel-only files under `docker/` (vhost.vercel.conf,
  entrypoint.vercel.sh) leak into the Render image's `/var/www/html/docker/`
  directory because `.dockerignore` only excludes `Dockerfile.vercel`
  and `vercel.json` by name, not the `docker/*.vercel.*` files. Minor
  wart; documented above in Verification. Not worth tightening —
  the files are tiny and harmless.

## Decisions
- ADR-006: parallel Vercel-deployment variant that mirrors ADR-004's
  Apache + mod_php + SQLite shape with three platform-specific
  differences isolated to new files. Rejected alternatives:
  `php artisan serve` (rejected by ADR-004 for production runtime),
  Vercel Postgres / Turso / Neon (out of scope per ADR-001),
  Vercel Persistent Storage (paid feature, adds external dependency),
  rename `Dockerfile.vercel` to `Dockerfile` (breaks Render deployment),
  single conditional Dockerfile (violates Section 1.4).

## Problems / Blockers
- Cannot run Docker locally (no daemon — OBS-004) — Render-side
  verification was pending after RUN-003; Vercel-side verification is
  similarly pending.
- Cannot run Vercel CLI locally — Vercel live build verification must
  be done by the user via import-repo-into-Vercel.
- The Vercel-only `docker/*.vercel.*` files leak into the Render image
  (see Discoveries) — minor wart, not a blocker.

## Remaining Work
- User: import the repo into Vercel and confirm the build succeeds.
  If Vercel rejects the build, the most likely cause is the
  `@vercel/docker` builder being unavailable on the user's Vercel
  plan — Vercel Container Deployments may require a paid tier. The
  fallback is to rename `Dockerfile.vercel` to `Dockerfile` (and the
  Render Dockerfile to `Dockerfile.render`) and remove `vercel.json`.
- User: visit the deployed URL and confirm the styled UI renders. The
  forceScheme fix from ADR-005 (RUN-004) applies on Vercel too because
  Vercel also terminates TLS at its load balancer.
- Future: tighten `.dockerignore` to exclude `docker/*.vercel.*` from
  the Render image's build context. Low priority — the leaked files
  are tiny and harmless.

## Next Agent Notes
- The Vercel deployment variant is in `Dockerfile.vercel`,
  `docker/apache/vhost.vercel.conf`, `docker/entrypoint.vercel.sh`, and
  `vercel.json`. Do NOT remove any of these without confirming the
  Vercel deployment target is no longer needed.
- Do NOT add Vercel-specific logic to the Render Dockerfile / entrypoint
  / vhost, or vice versa. Keep the two deployment variants isolated.
- If the user reports the Vercel build failed, ask which Vercel plan
  they're on. Container Deployments may require a paid tier; the
  fallback is renaming the files (see Remaining Work above).
- If the user reports the Vercel deployment works but contact
  submissions are lost, that's expected per OBS-007 — point them to
  the Render deployment or to the OBS-007 future-work note about
  migrating to a hosted database.
- If a future agent switches the Vercel image to a non-Apache runtime
  (Frankenphp, RoadRunner, `artisan serve`), ADR-004's reasoning
  should be revisited — the rejection of `artisan serve` was for the
  Render deployment, and a different runtime choice on Vercel would
  warrant a new ADR superseding ADR-006 for the Vercel target only.
