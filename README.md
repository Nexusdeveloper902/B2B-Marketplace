# Presence Platform — Marketplace Storefront

The marketing/sales storefront for **Presence Platform**, a school/enterprise NFC
presence-event product (attendance, PAE meal tracking, recycling incentives,
custom event tracking). This site sells the product — it is deliberately not the
product itself, and it has no dependency on the core platform's backend or
hardware. See `.agent/PROJECT.md` for the full project context.

- **Framework:** Laravel 13 + Blade (no Livewire/Inertia/SPA, no Node build pipeline)
- **Storage: NONE — deliberately stateless.** There is no database of any kind
  (see `.agent/DECISIONS/ADR-013-stateless-no-database.md`). Contact requests are
  written to the application log, not persisted.
- **Languages:** English and Spanish — toggle **EN / ES** in the header
- **No auth, no payments, no multi-vendor mechanics**

## Pages

| Route | Page |
|---|---|
| `/` | Landing — problem, pitch, how it works, applications |
| `/product` | Product overview — the tap-to-report pipeline, event anatomy |
| `/pricing` | Packages — Starter / Campus / Enterprise |
| `/enterprise` | Enterprise — custom labeled-event tracking |
| `/contact` | Request a demo — validated form, submission logged (no DB) |

## Quickstart

Requires PHP >= 8.3 (with `mbstring`, `openssl`, `tokenizer`, `dom`) and
Composer. Nothing else — **no Node, no database server, no mail, no
migrations.**

```bash
composer install
cp .env.example .env        # the repo does not ship .env (ADR-013)
php artisan key:generate --force
php artisan serve
```

Then open `http://127.0.0.1:8000`.

### Why there is no database

This product decision is explicit and binding (ADR-013, superseding ADR-001):
the storefront is a marketing site whose only dynamic endpoint is a contact
form. A database would add a persistence surface, a migration step, and a
deployment dependency for data nobody operates. Validated submissions are
written to the application log (`storage/logs/laravel.log` locally, stderr on
Vercel) so the operator can pick them up from the deployment's log drain. If
real lead capture is ever needed, add a hosted form backend or a managed DB —
as a separate, explicit decision.

## Deploy with Docker (Render)

The storefront ships as a self-contained production image — Apache + mod_php,
self-hosted fonts; no Node, no database, no other services. The Render /
docker-compose Dockerfile is the repo's default `Dockerfile`:

```bash
docker build -t presence-platform-storefront .
docker run --rm -p 8080:80 presence-platform-storefront
# storefront at http://localhost:8080
```

The entrypoint prepares writable directories, materializes `.env` from
`.env.example`, generates an `APP_KEY` at startup when none is provided via
environment, then hands off to Apache. **No migrations run — there is no
database.** Contact requests appear in the container log
(`docker compose logs storefront`).

## Deploy on Vercel (Container Service — FrankenPHP)

Vercel has no first-class Laravel runtime, but it can run any Docker image as
a **container service** via `Dockerfile.vercel` + a `vercel.json` that
declares `runtime: "container"`. The Dockerfile uses **FrankenPHP**
(Vercel's officially recommended PHP runtime per
`vercel.com/kb/guide/deploy-php-on-vercel-with-docker`) — a single binary
combining the Caddy web server and PHP runtime that reads environment
variables natively and binds to `$PORT` via Caddyfile config.

Previous attempts used Apache+mod_php, which failed because the `php:apache`
image does NOT pass OS env vars to PHP by default (requiring `SetEnv`
directives that were never added). FrankenPHP eliminates this issue entirely.
See `.agent/OBSERVATIONS/OBS-012-apache-mod-php-env-var-passing.md` and
`.agent/DECISIONS/ADR-011-switch-to-frankenphp.md` for the full analysis.

Vercel-specific files:

| File | Purpose |
|---|---|
| `Dockerfile.vercel` | Multi-stage FrankenPHP image (Vercel detects this filename) |
| `vercel.json` | Declares the container service with `runtime: "container"` + catch-all rewrite |
| `docker/caddy/Caddyfile.vercel` | Caddy config: listens on `:{$PORT:80}`, serves `/app/public`, routes through `index.php` |
| `docker/entrypoint.frankenphp.sh` | Entrypoint: creates writable dirs in `/tmp`, loads env, generates `APP_KEY`, execs FrankenPHP |

### Quick deploy

1. Push this commit to `main` (already done if you're reading the repo).
2. Import the repo into Vercel — Vercel detects `Dockerfile.vercel` and the
   `vercel.json` `services` declaration, builds the image, and serves it as
   a container function.
3. No env vars required for a working demo — `APP_KEY` is auto-generated on
   cold start if absent (pass `APP_KEY` in Vercel project settings to keep
   keys stable across cold starts; a fresh key only resets cookie sessions,
   which this site does not rely on).
4. The `vercel.json` is **required** — without the `services` +
   `runtime: "container"` declaration, Vercel falls back to framework
   auto-detection and the Dockerfile is never used.

### ⚠️ Deployment notes

Vercel's container filesystem is **ephemeral**. The entrypoint relocates
Laravel's `storage/` tree to `/tmp/`, so the app runs correctly during a
single container lifetime.

Since the storefront is **stateless** (ADR-013), this no longer costs any
data: there is no database to lose. Contact requests go to the container's
stderr log, which Vercel captures in its log drain — submissions survive in
the logs even though the (nonexistent) filesystem does not. The former
caveat about losing SQLite submissions on cold restart is obsolete.

The `/__debug` diagnostic endpoint that existed during the Vercel bring-up
(RUN-005..RUN-011) was **removed** in TASK-011 — it was unauthenticated and
leaked environment details. Deployment diagnosis now uses the Vercel build
and runtime logs.

### Local test of the Vercel image

```bash
docker build -f Dockerfile.vercel -t storefront-vercel .
docker run --rm -p 8080:8080 -e PORT=8080 storefront-vercel
# storefront at http://localhost:8080
```

## Tests

```bash
php artisan test
```

No local setup needed — `phpunit.xml` carries a disposable test-only
`APP_KEY` so a fresh clone can run the suite immediately.

## Design

The visual direction is **"The Event Ledger"**: the storefront's grammar is
built from the product's own artifact — the timestamped, labeled event record.
Porcelain paper ground, deep institutional green, hairline-ruled sections
instead of floating cards, Space Grotesk / IBM Plex Sans / IBM Plex Mono
(self-hosted in `public/fonts`, so the demo works offline), and a single
orchestrated motion moment in the hero (card → go-light → row written).
Do not revert it to default template aesthetics — see
`.agent/DECISIONS/ADR-002-visual-design-direction.md`.

## Repository layout for agents

Persistent project memory lives in `.agent/` (append-only): task records,
run reports, ADRs, observations, and state snapshots. Read
`.agent/PROJECT.md` and `.agent/TASKS/TASK-001-marketplace-mvp.md` before
changing anything, and append — never rewrite — historical records.
