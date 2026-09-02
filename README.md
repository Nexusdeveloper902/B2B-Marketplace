# Presence Platform — Marketplace Storefront

The marketing/sales storefront for **Presence Platform**, a school/enterprise NFC
presence-event product (attendance, PAE meal tracking, recycling incentives,
custom event tracking). This site sells the product — it is deliberately not the
product itself, and it has no dependency on the core platform's backend or
hardware. See `.agent/PROJECT.md` for the full project context.

- **Framework:** Laravel 13 + Blade (no Livewire/Inertia/SPA, no Node build pipeline)
- **Storage:** SQLite, one table (`contact_requests`) for contact/demo requests
- **Languages:** English and Spanish — toggle **EN / ES** in the header
- **No auth, no payments, no multi-vendor mechanics**

## Pages

| Route | Page |
|---|---|
| `/` | Landing — problem, pitch, how it works, applications |
| `/product` | Product overview — the tap-to-report pipeline, event anatomy |
| `/pricing` | Packages — Starter / Campus / Enterprise |
| `/enterprise` | Enterprise — custom labeled-event tracking |
| `/contact` | Request a demo — validated form, persisted to SQLite |

## Quickstart

Requires PHP >= 8.3 (with `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`,
`dom`) and Composer. Nothing else — no Node, no database server, no mail.

```bash
composer install
php artisan migrate
php artisan serve
```

Then open `http://127.0.0.1:8000`. The SQLite database file is created
automatically by the migration. `.env` ships with the repository because this
app contains no secrets (local SQLite, no mail/API credentials).

## Deploy with Docker (Render / persistent volume)

The storefront ships as a self-contained production image — Apache + mod_php,
SQLite, self-hosted fonts; no Node, no database server, no other services.
The Render / docker-compose Dockerfile is the repo's default `Dockerfile`:

```bash
docker build -t presence-platform-storefront .
docker run --rm -p 8080:80 presence-platform-storefront
# storefront at http://localhost:8080
```

The entrypoint prepares writable directories, creates and migrates the SQLite
database on first start (idempotently on every start), then hands off to
Apache. The tracked `.env` is part of the image and contains no secrets (local
SQLite, no mail/API credentials). `docker-compose.yml` places the database on
a persistent volume by passing `DB_DATABASE`, which the entrypoint applies to
the app configuration at startup; any other variable you pass with
`-e KEY=value` is available to the entrypoint and CLI.

For a persistent deployment — contact submissions survive rebuilds:

```bash
docker compose up -d --build       # builds from Dockerfile, migrates, serves on :8080
docker compose logs -f storefront
```

`docker-compose.yml` keeps the SQLite file on the `storefront-data` volume
and adds a healthcheck against Laravel's `/up` route. Publish on a different
port with `APP_PORT=3000 docker compose up -d`.

## Deploy on Vercel (Container Service)

Vercel has no first-class Laravel runtime, but it can run any Docker image as
a **container service** via `Dockerfile.vercel` + a `vercel.json` that
declares `runtime: "container"`. This is the **only** correct way to deploy
a Docker image on Vercel — a bare `Dockerfile` is treated as a generic
serverless function (different semantics, different filesystem model, no
guaranteed entrypoint execution). See
`.agent/OBSERVATIONS/OBS-011-vercel-requires-dockerfile-vercel-and-services-config.md`
for the full analysis of why previous approaches failed.

Vercel-specific files:

| File | Purpose |
|---|---|
| `Dockerfile.vercel` | Multi-stage Apache + mod_php image (Vercel detects this filename) |
| `vercel.json` | Declares the container service with `runtime: "container"` + catch-all rewrite |
| `docker/apache/vhost.vercel.conf` | Vhost template with a `{{PORT}}` placeholder the entrypoint substitutes at startup |
| `docker/entrypoint.vercel.sh` | Entrypoint variant that relocates writable state to `/tmp`, patches Apache's port from `$PORT`, and skips `chown` when not root |

### Quick deploy

1. Push this commit to `main` (already done if you're reading the repo).
2. Import the repo into Vercel — Vercel detects `Dockerfile.vercel` and the
   `vercel.json` `services` declaration, builds the image, and serves it as
   a container function.
3. No env vars required for a working demo — `APP_KEY` is auto-generated on
   cold start if absent.
4. The `vercel.json` is **required** — without the `services` +
   `runtime: "container"` declaration, Vercel falls back to framework
   auto-detection and the Dockerfile is never used.

### ⚠️ Critical caveat: data is ephemeral

Vercel's container filesystem is **ephemeral**. The entrypoint relocates
the SQLite database file and Laravel's `storage/` tree to `/tmp/`, so
the app runs correctly during a single container lifetime — but on every
cold restart (redeploy, scale-to-zero after 5 min idle, container recycle),
**all contact submissions are lost** and the database is recreated from
migrations.

This is acceptable for a marketing storefront demo. For production use,
either:

- Stay on Render (persistent SQLite volume via `docker-compose.yml`), or
- Move contact persistence to Vercel Postgres / Turso / Neon and update
  `config/database.php` + the `ContactRequest` model accordingly
  (out of scope per ADR-001 / TASK-001 constraints).

See `.agent/OBSERVATIONS/OBS-007-vercel-ephemeral-filesystem.md` and
`.agent/DECISIONS/ADR-010-vercel-services-runtime-container.md` for the
full rationale.

### Local test of the Vercel image

```bash
docker build -f Dockerfile.vercel -t storefront-vercel .
docker run --rm -p 8080:8080 -e PORT=8080 storefront-vercel
# storefront at http://localhost:8080
```

### Debug endpoint

Once deployed, visit `/__debug` on the Vercel URL to see DB path, file
writability, storage writability, and PHP extensions as JSON (no DB
access required). Useful for diagnosing deployment issues.

## Tests

```bash
php artisan test
```

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
