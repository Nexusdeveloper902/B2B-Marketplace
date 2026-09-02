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

## Deploy with Docker

The storefront ships as a self-contained production image — Apache + mod_php,
SQLite, self-hosted fonts; no Node, no database server, no other services.
Build and run it directly:

```bash
docker build -t presence-platform-storefront .
docker run --rm -p 8080:80 presence-platform-storefront
# storefront at http://localhost:8080
```

The entrypoint prepares writable directories, creates and migrates the SQLite
database on first start (idempotently on every start), then hands off to
Apache. The tracked `.env` is part of the image and contains no secrets; any
variable passed with `-e KEY=value` overrides it at runtime.

For a persistent deployment — contact submissions survive rebuilds:

```bash
docker compose up -d --build       # builds, migrates, serves on :8080
docker compose logs -f storefront
```

`docker-compose.yml` keeps the SQLite file on the `storefront-data` volume
and adds a healthcheck against Laravel's `/up` route. Publish on a different
port with `APP_PORT=3000 docker compose up -d`.

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
