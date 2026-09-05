# Activity Ledger — RUN-2026-09-05-marketplace-013

## 2026-09-05 (times are session-relative)
ACTION: Resume investigation
COMMAND: ls -la; git status --short; git log --oneline -20; git branch -a; git remote -v
RESULT: .agent/ complete (RUN-002..012, ADR-001..012, TASK-001..010,
OBS-001..016). main clean at ecde2d5, single-commit history, origin =
github.com/Nexusdeveloper902/B2B-Marketplace. No RUN-...-001 record exists
(runs on file start at 002) — prior-run docs were NOT lost; the run brief's
numbering was stale. This run continues as 013.

ACTION: Diagnose 172 modified files
COMMAND: git diff --stat; git config core.filemode
RESULT: pure mode noise (100644->100755), 0 insertions/deletions. Fixed
locally with `git config core.filemode false`. Nothing committed.

ACTION: Recover prior task documentation
FILES: .agent/PROJECT.md, .agent/STATE/SNAPSHOT-…012.md,
.agent/RUNS/RUN-…012.md, .agent/OBSERVATIONS/OBS-002 (full reads)
RESULT: FOUND — SNAPSHOT-012 confirms Vercel WORKING, /__debug flagged
"remove before production", Next-Agent-Notes: do not touch env-loading
block or storage overrides.

ACTION: Toolchain re-provision
COMMAND: curl dl.static-php.dev/common/php-8.4.8-cli-linux-x86_64.tar.gz; curl getcomposer.org latest-stable
RESULT: /home/z/my-project/tools/{php,composer.phar} — PHP 8.4.8 CLI,
Composer 2.10.3 (OBS-002 recipe; previous tools lost to ephemeral sandbox).

ACTION: Baseline verification
COMMAND: composer install; php artisan route:list; php artisan test
RESULT: 79 packages; 12 routes incl. /__debug; 14 tests passed
(101 assertions). main buildable.

ACTION: Read deployment-critical files
FILES: Dockerfile, Dockerfile.vercel, vercel.json, docker-compose.yml,
docker/entrypoint.sh, docker/entrypoint.frankenphp.sh, .dockerignore,
.gitignore, phpunit.xml, .env.example, routes/web.php, tests/, ContactRequest
RESULT: KEY FINDING — committed .env was the Vercel APP_KEY source; both
entrypoints have cp .env.example fallback but the Vercel cp would crash
under set -e on the read-only FS (latent, never fired because tracked .env
shipped in image). Untracking .env therefore required: direct sourcing
fallback + APP_KEY generation (key:generate --show, no FS write).

ACTION: Branch
COMMAND: git checkout -b feature/TASK-011-stateless-storefront
RESULT: SUCCESS

ACTION: Commit 1
COMMIT: 8bced93
RESULT: DB layer removed; contact->log; entrypoints/Docker/compose/envs
updated; ADR-013, README, PROJECT.md, lang copy. Tests: 14 passed (103).
sh -n both entrypoints: PASS.

ACTION: Commit 2
COMMIT: 66971a7
RESULT: /__debug removed; throttle:5,1 on POST /contact; .env untracked
(git rm --cached) + /.env gitignored. Tests: 14 passed (103). route:list
shows no debug route.

ACTION: Commit 3
COMMIT: 5ce315a
RESULT: StatelessArchitectureTest (3 tests). Initial run: 1 failure —
ErrorException from stale vendor classmap include of deleted model;
resolved with composer dump-autoload (sandbox artifact, not a repo issue).
Final: 17 passed (120 assertions).

ACTION: Memory records
FILES: .agent/TASKS/TASK-011-stateless-storefront.md (new),
.agent/RUNS/RUN-2026-09-05-marketplace-013.md + .ledger.md (new),
.agent/STATE/SNAPSHOT-…013.md (new), .agent/OBSERVATIONS/OBS-017… (new)
RESULT: SUCCESS (append-only; no historical file modified)

ACTION: Merge
SOURCE: feature/TASK-011-stateless-storefront
TARGET: main
RESULT: (recorded after merge)

ACTION: Main verification
COMMAND: php artisan test (on main)
RESULT: (recorded after merge)

ACTION: Push
COMMAND: git push origin feature/TASK-011-stateless-storefront main
RESULT: (recorded after push)

## 2026-09-05 (post-merge catch)
ACTION: Main verification (first attempt)
COMMAND: php artisan test (on main after merge 353d3e6)
RESULT: FAIL — 14 failed, MissingAppKeyException. Fresh checkout had no
.env (merge deleted the tracked file); feature-branch runs were masked by
the untracked .env still on disk. Root cause: untracking .env removed the
APP_KEY source for bare local runs (Docker paths unaffected — entrypoints
generate keys).

ACTION: Repair per §7.10
COMMAND: git checkout feature/…; patch phpunit.xml (test-only APP_KEY) +
README quickstart (cp .env.example .env; key:generate); fresh-clone
simulation; live serve smoke test on :8031
RESULT: 17 passed / 120 assertions; 5 pages 200; /__debug 404; POST w/o
CSRF token -> 419 (CSRF confirmed); contact.request in logs

ACTION: Commit 5
COMMIT: 87581e4
RESULT: SUCCESS — re-merge to main follows; records updated before push.
