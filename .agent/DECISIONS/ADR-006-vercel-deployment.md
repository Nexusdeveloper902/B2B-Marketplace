# ADR-006

## Date
2026-09-02

## Context
The client wants to deploy the storefront on Vercel. Vercel has no
first-class Laravel runtime — its native runtimes are Next.js, Node,
Python, Go, Ruby. The only path to a Laravel app on Vercel is via
Vercel Container Deployments: Vercel builds a Docker image from the
repo and runs it as a long-running container.

Vercel Container Deployments impose three constraints that the existing
`Dockerfile` (per ADR-004, built for Render) does not satisfy:

1. **Port injection.** Vercel injects a `$PORT` env var (typically 8080)
   and routes HTTP traffic to whatever the container listens on. Apache's
   `Listen` directive cannot read env vars at parse time, so the existing
   `*:80` vhost in `docker/apache/vhost.conf` cannot be reused as-is.

2. **Ephemeral filesystem.** Vercel container instances have a writable
   but ephemeral filesystem: writes during runtime are NOT preserved
   across cold starts (redeploys, scale-to-zero cycles, container
   recycles). The existing entrypoint writes SQLite + Laravel's
   `storage/` tree into `/var/www/html/...` — that location may be
   read-only in Vercel's container runtime, and even if writable, the
   data is lost on cold restart anyway.

3. **Non-root execution.** Vercel may invoke the container as a non-root
   user. The existing entrypoint's `chown -R www-data:www-data ...` step
   fails silently when not root (no permission); the fix is to make the
   chown conditional on `id -u = 0`.

In addition, Vercel auto-detects a `Dockerfile` in the repo root. The
existing `Dockerfile` is the Render deployment (ADR-004) and must
continue to work for that target. A second Dockerfile requires either
renaming or a `vercel.json` that points Vercel at the alternate file.

## Decision
Ship a parallel Vercel-deployment variant that mirrors ADR-004's Apache
+ mod_php + SQLite shape, with the Vercel-specific concerns isolated to
four new files:

- **`Dockerfile.vercel`** — multi-stage image built on `php:8.4-apache`,
  identical to `Dockerfile` except it copies `vhost.vercel.conf` and
  `entrypoint.vercel.sh` instead of the Render variants, and documents
  `EXPOSE 8080` (informational only — Vercel routes to the actual
  `$PORT`).
- **`docker/apache/vhost.vercel.conf`** — vhost template with a `{{PORT}}`
  placeholder that the entrypoint substitutes with `$PORT` at startup.
  Apache's `Listen` directive cannot read env vars at parse time, so
  templating is the only option.
- **`docker/entrypoint.vercel.sh`** — entrypoint variant that:
  1. Relocates Laravel's `storage/` tree to `/tmp/storefront/storage`
     (the only guaranteed-writable location on Vercel) via a symlink
     from `/var/www/html/storage`.
  2. Places the SQLite database file at `/tmp/storefront/database.sqlite`
     by default.
  3. Patches `vhost.vercel.conf`'s `{{PORT}}` and `/etc/apache2/ports.conf`'s
     first `Listen` directive with the actual `$PORT` value.
  4. Conditionally runs `chown -R www-data:www-data ...` only when the
     entrypoint is running as root (i.e. `id -u = 0`); skips silently
     otherwise.
  5. Otherwise mirrors `entrypoint.sh` — `APP_KEY` bootstrap, materialize
     `DB_DATABASE` into `.env` per OBS-005, idempotent `php artisan
     migrate --force`.
- **`vercel.json`** — wires Vercel's `@vercel/docker` builder at
  `Dockerfile.vercel` so Vercel picks up the Vercel variant instead of
  the default `Dockerfile` (which is the Render image).

`.dockerignore` is updated to exclude `Dockerfile.vercel`, `vercel.json`,
and the Vercel-only `docker/*` files from the Render image's build
context, so the Render image stays clean (Section 1.4 — minimize
unrelated changes).

The existing `Dockerfile`, `docker/apache/vhost.conf`, `docker/entrypoint.sh`,
and `docker-compose.yml` are NOT modified — the Render deployment continues
to work exactly as documented in ADR-004.

## Alternatives Considered
- **Switch the Vercel image to `php artisan serve` instead of Apache.**
  Pros: `artisan serve --port=$PORT` natively respects the env var; no
  vhost templating needed. Cons: ADR-004 explicitly rejected `artisan
  serve` as a production runtime ("development server — rejected").
  Consistency with ADR-004 wins; the vhost templating is a one-time
  entrypoint concern, not a recurring cost. Rejected.
- **Use Vercel Postgres / Turso / Neon instead of SQLite on Vercel.**
  Pros: persistent contact submissions across cold starts. Cons: violates
  ADR-001 (SQLite only) and TASK-001's explicit "out of scope: switching
  the database off SQLite for any reason." The storefront is a marketing
  demo, not a production contact-management system — ephemeral submissions
  are acceptable for the demo. Rejected; documented as future work in
  OBS-007 if production persistence is ever needed.
- **Use Vercel Persistent Storage (attached volumes).** Pros: preserves
  SQLite across cold starts without changing the DB. Cons: paid Vercel
  feature, requires dashboard configuration outside the repo, and adds
  an external dependency for what is otherwise a self-contained app.
  Documented as a future option in OBS-007; not the default.
- **Rename `Dockerfile.vercel` to `Dockerfile` and the Render Dockerfile
  to `Dockerfile.render`.** Pros: Vercel auto-detects without `vercel.json`.
  Cons: breaks the Render deployment that's already running with the
  existing `Dockerfile` name; requires updating `docker-compose.yml`,
  Render's dashboard config, and the README. Section 1.4 (minimize
  unrelated changes) — rejected.
- **Conditional logic in a single `Dockerfile` + `entrypoint.sh` that
  detects the target platform.** Pros: one file. Cons: violates
  Section 1.4 (don't add unrelated complexity); platform detection at
  runtime is fragile (e.g. `if [ -n "$PORT" ]` is true on Render too if
  the user sets PORT). Rejected in favor of parallel files with clear
  single-purpose roles.

## Reasoning
The Vercel variant's constraints are platform-specific (port injection,
ephemeral FS, non-root) and orthogonal to the Render deployment's concerns
(persistent volume, root Apache, port 80). A parallel file set keeps each
platform's logic isolated and readable, lets the Render deployment continue
unchanged, and lets a future agent remove either variant without touching
the other.

The ephemeral-filesystem consequence (lost contact submissions on cold
restart) is acceptable for this app's purpose: the storefront's only
business data is contact/demo requests, and the app is explicitly a
marketing/sales surface, not a production contact-management system
(see `.agent/PROJECT.md`). The data-loss caveat is documented loudly
in the README, OBS-007, and the entrypoint comments so no future agent
silently assumes persistence.

## Consequences
- Vercel deployment is `git push` + import-repo-into-Vercel — no manual
  image build required. Vercel builds `Dockerfile.vercel` via the
  `@vercel/docker` builder declared in `vercel.json`.
- The Render deployment is unaffected. Both deployment targets share the
  same application code, the same migration, the same Blade templates,
  the same self-hosted fonts. Only the Dockerfile + entrypoint + vhost
  differ.
- Contact submissions made on the Vercel deployment are EPHEMERAL.
  Anyone using the Vercel deployment for real demo collection should
  either: (a) accept the data-loss caveat for short-lived demos, or
  (b) move to Render's persistent-volume deployment, or (c) migrate
  to a hosted database (out of scope per ADR-001).
- Future agents must NOT remove `Dockerfile.vercel`, `vercel.json`,
  `docker/apache/vhost.vercel.conf`, or `docker/entrypoint.vercel.sh`
  without confirming the Vercel deployment target is no longer needed.
- Future agents must NOT add Vercel-specific logic to the Render
  Dockerfile / entrypoint / vhost, or vice versa. Keep the two
  deployment variants isolated.
- If a future agent switches the Vercel image to a non-Apache runtime
  (e.g. Frankenphp, RoadRunner, artisan serve), ADR-004's reasoning
  should be revisited — the rejection of `artisan serve` was for the
  Render deployment, and a different runtime choice on Vercel would
  warrant a new ADR superseding this one for the Vercel target only.

## Status
ACTIVE

## Supersedes
none
