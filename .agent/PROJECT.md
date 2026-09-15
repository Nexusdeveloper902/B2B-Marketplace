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

## TASK-013 additions (2026-09-07, RUN-2026-09-07-marketplace-015)

- **Design system v3 "Datum" (ADR-015, supersedes ADR-014)**: the
  storefront now shares the Core dashboards' light sage M3 palette,
  Epilogue/Manrope/Space Grotesk/IBM Plex Mono type system, and 2/4/8px
  radius ladder. Storefront-specific: data-green semantic family for
  event labels/live states, dark inverse footer + closing band, gold
  tertiary accents.
- **CSS-only implementation**: app.css rewritten, fonts.css extended,
  4 variable-font woff2 vendored from Core's vetted set. ZERO changes
  to Blade views, controllers, routes, tests, or public/js/app.js —
  content preservation is structural (86/86 audit; 17/120 tests
  unchanged).
- **The motion layer's runtime contract is now public API** (ADR-015):
  tokens --data/--data-solid/--data-tint/--surface/--border-strong/
  --accent-soft and classes .is-revealed/.is-new/.is-hit/.is-on must
  survive every future stylesheet revision.
- **Gap ledger shipped**: docs/FRONTEND.md + FRONTEND.es.md — 18
  documented aspirational elements (cart/checkout, lead persistence,
  email/CRM, testimonials, real feed, status, demo sandbox, scheduling,
  analytics, sitemap, extra locales, legal pages) each with why it is
  absent and what it would need. Honesty floor: no fake data, no dead
  buttons.
- **Durable traps**: sandbox kills backgrounded servers between tool
  calls (server + all server-dependent checks in ONE invocation);
  content_audit.py targets port 8099; `cd x && cmd &` backgrounds the
  whole chain; B2B-Core's hermetic .tools/php serves this repo's
  artisan/composer fine.
- **Sibling-repo audit (owner distrust, same session)**: B2B-Core
  d3a94c0 ci 3/3 (275/1-skip/4442, e2e 33/33); B2B-Firmware d657265
  (owner bench commit) native 90/90 + 3 envs SUCCESS; ESP32-CAM-CV
  5c62dc1 pytest 114/1-skip + build ±secrets SUCCESS. Every claim of
  the latest commits reproduces.

## RUN-2026-09-12-marketplace-016 — Pulse brand adoption (appended facts)
- Storefront rebranded to Pulse per the owner's productization pass:
  real mark in header (ink) and footer (cream mono variant), full
  favicon/PWA/OG head + manifest, lang EN+ES sweep, READMEs,
  APP_NAME "Pulse Marketplace". Placeholder favicon.svg deleted.
- Suite 20/20 (PagesTest now pins "Pulse"). Uncommitted; Core's
  RUN-2026-09-12-core-036 + ADR-047 carry the family-wide identity
  contract; this repo's ADR-017 records the storefront adoption.

## RUN-2026-09-13-marketplace-017 — Landing panel fix + translations + 404 (appended facts)

- Owner items: gate-reader panel position, translations, search.
- `.tap-visual` centers the card+reader pair (bounded clamp gap, 28px
  insets) — the reader no longer hugs the panel's right border;
  `ledgerLoop.distance()` measures the real offsetLeft gap so the card
  still stops just short of the reader at every width.
- Landing demo strings translated: `identify_join_prefix/event`,
  `stamp_recorded` (EN/ES). Suite 20/20.
- Branded localized 404 added (errors/404.blade.php + `.nf-*` CSS +
  `common.not_found_*` keys) — unknown URLs no longer render Laravel's
  bare page.

## RUN-2026-09-13-marketplace-017 follow-up — global box-sizing reset
- Owner corrected the panel: the Tap/Toque DEMO card's gate reader was
  OUTSIDE the demo frame. Root cause: the storefront never had a global
  `box-sizing: border-box` (content-box default) — width:100% + 48px
  padding made `.demo-tap-stage` 47px wider than `#demo-tap`, clipping
  the reader. Global reset added; the three per-element copies remain
  (harmless).
- Post-reset sweep caught /product @ 390px +26px: `.closing-in` grid
  blowout (minmax(0,1fr) fix) and the nowrap closing CTA with the long
  ES label (scoped white-space: normal).
- Verified: demo reader 25px inside the frame; 6 URLs × 3 widths = 0
  overflow; suite 20/20.

## TASK-014 additions (2026-09-15)

- **Marketplace catalog layer (ADR-018)**: the site is structured as a
  catalog (listings, comparison matrix, PDP buy box, use-case shelf) on
  the unchanged Datum palette. Copy stays in the existing lang files;
  `store.php` is UI labels only — never add product facts there.
- **Quote list** is client-side only (localStorage) and outputs a
  prefilled `/contact` request; it is not a cart and must not grow
  prices or checkout without a new ADR (ADR-013 still binds).
