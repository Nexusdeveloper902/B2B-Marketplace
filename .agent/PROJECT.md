# Project: Presence Platform — Marketplace Storefront

## What this is
A standalone marketing/sales storefront for "Presence Platform" — a school/enterprise
NFC-based presence-event system (attendance, PAE meal tracking, recycling incentives,
custom event tracking). This storefront is DELIBERATELY NOT the product itself. It
exists to sell the product, not to run it.

## Explicit non-goals
- This app does NOT integrate with the core platform backend/API.
- This app does NOT need real payment processing, cart, or checkout.
- This app does NOT need user accounts, login, or multi-vendor seller listings.
- This is a single-vendor storefront (one company, tiered packages) — not a
  multivendor marketplace in the literal sense, despite the "marketplace" framing
  used internally on this project.

## Relationship to the core platform
Independent codebase. Independent deploy. The only relationship is: this app links
out to a "Request a Demo" flow and describes the product the platform team is
building. Nothing here should ever block or depend on the platform build, and
nothing in the platform build should ever depend on this app.

## Data
STATELESS — this app has NO database (decision of 2026-09-05, see
`.agent/DECISIONS/ADR-013-stateless-no-database.md`, which supersedes
ADR-001-sqlite-over-server-database.md). Contact/demo requests are validated
server-side and written to the application log (stderr on Vercel,
`storage/logs/laravel.log` on Render/local) — they are not persisted. There
are no migrations, no models, and no DB env vars. Do not reintroduce a
database without a new explicit ADR.

## Design bar
This app is judged partly on visual presentation. It must look like a deliberately
designed, premium product site — not a default-template or visibly AI-generated
page. See the task spec's "UI / Visual Quality Requirements" for specifics, and
`.agent/DECISIONS/ADR-002-visual-design-direction.md` for the recorded direction.

## Languages
The storefront ships fully bilingual: English (default) and Spanish. A visible
EN/ES toggle in the header switches locale (session-persisted). All copy lives in
`lang/en/` and `lang/es/`. See `.agent/DECISIONS/ADR-003-bilingual-en-es.md`.

## Runtime notes (this execution environment)
- The environment has no system PHP/Composer. A static PHP 8.4 CLI binary and
  Composer phar are installed at `/home/z/my-project/tools/php` and
  `/home/z/my-project/tools/composer.phar`. Use those binaries for artisan/composer.
  See `.agent/OBSERVATIONS/OBS-002-static-php-toolchain.md`.
- Fonts are self-hosted (`public/fonts/*.woff2`, `public/css/fonts.css`) so the demo
  works offline with no CDN dependency.
