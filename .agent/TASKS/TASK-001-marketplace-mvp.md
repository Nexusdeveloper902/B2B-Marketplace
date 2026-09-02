# TASK-001-marketplace-mvp

## Task
Build the Presence Platform marketplace storefront MVP: a Laravel marketing/sales
site with exactly five pages (Landing `/`, Product `/product`, Pricing `/pricing`,
Enterprise `/enterprise`, Contact `/contact`) linked by shared nav/footer, backed by
SQLite with a single `contact_requests` table for contact/demo requests. Bilingual
EN/ES (extra requirement added for RUN-2026-09-02-marketplace-002). Deliberate,
product-specific visual design per the UI quality bar (see ADR-002).

## Constraints (summary)
- Laravel + Blade only. No Livewire/Inertia/SPA. No Node/Vite build pipeline (plain CSS).
- SQLite only. Exactly one migration (`contact_requests`).
- No auth, no payments, no multi-vendor mechanics, no platform-backend integration.
- Time budget: 1–2 days equivalent total across all runs.

## Status
IN PROGRESS — RUN-2026-09-02-marketplace-002

## Commit Log

(appended below per commit — this file is append-only)

## Commit — 1dbbe7f

Date: 2026-09-02
Branch: main

Summary:
Initial baseline (created by RUN-2026-09-02-marketplace-002, not by the interrupted
prior run): pristine Laravel 13.30.1 scaffold via `composer create-project`, with
`.env` committed (no secrets exist in this app; APP_KEY present so a fresh clone
boots with zero manual setup) and `database/database.sqlite` git-ignored so
`php artisan migrate` creates it from scratch.

Changes:
- Laravel 13 scaffold (default migrations: users/cache/jobs — removed later on the
  feature branch to keep the DB footprint to exactly one table)
- `.gitignore`: un-ignored `.env`, added `/database/database.sqlite`

Verification:
- `php artisan migrate` auto-creates the sqlite file: PASS
- `php artisan serve` + GET `/` returns the welcome view: PASS

Notes:
- The repository was found COMPLETELY EMPTY at the start of this run (no commits,
  no branches, no `.agent/`). The interrupted prior run RUN-2026-09-01-marketplace-001
  left no recoverable artifacts; nothing could be salvaged or verified from it. This
  run therefore built from scratch. See RUNS/RUN-2026-09-02-marketplace-002.md.

## Commit — c9c3166

Date: 2026-09-02
Branch: feature/TASK-001-marketplace-mvp
Run: RUN-2026-09-02-marketplace-002

Summary:
The complete five-page bilingual storefront, implemented in the "Event Ledger"
design direction (ADR-002) with EN/ES bilingual mode (ADR-003).

Changes:
- Routes: /, /product, /pricing, /enterprise, /contact (GET+POST), /contact/thank-you,
  /lang/{locale}
- Controllers: PageController, ContactController (validation + persist),
  LocaleController (session locale switch)
- Middleware: SetLocale (session preference, then Accept-Language, then default en)
- Model + single migration: ContactRequest / contact_requests (SQLite only table)
- Views: layouts/app, partials/header+footer (with EN|ES toggle and JS-free mobile
  details menu), pages: landing, product, pricing, enterprise, contact,
  contact-thank-you
- Copy: lang/en/* and lang/es/* (nav, common, landing, product, pricing,
  enterprise, contact, forms incl. localized validation messages)
- Design system: public/css/app.css (porcelain/pine palette, hairline-ruled
  sections, Space Grotesk / IBM Plex Sans / IBM Plex Mono), self-hosted woff2
  fonts in public/fonts, favicon.svg
- Removed scaffold welcome view and default favicon

Verification:
- All 6 routes return 200 via php artisan serve + curl: PASS
- EN default; /lang/es switches and persists across pages (html lang=es, Spanish
  copy on /, /pricing, /contact): PASS
- Contact form valid POST -> 302 to thank-you, row persisted in SQLite
  (verified via tinker), email echoed on thank-you: PASS
- Invalid POST -> redirect back with EN/ES localized errors + old input: PASS
- Horizontal overflow audit at 360px/390px/1440px via headless browser DOM
  measurement: no overflow on any page: PASS
- VLM design reviews (landing, pricing, mobile): defects found and fixed
  (ledger grid-item min-width overflow on mobile; footer legal contrast;
  hardware legibility of tap card/reader; featured-tier CTA weight): PASS
- CSS animations confirmed running via getAnimations/getComputedStyle
  (tapmove, golight, rowwrite), with prefers-reduced-motion fallback: PASS

Notes:
- Root cause of the mobile overflow found by measurement: .ledger is a grid item
  with default min-width:auto and its mono caption has a 442px min-content;
  fixed with min-width:0 (see OBS-003).

## Commit — 84ac297

Date: 2026-09-02
Branch: feature/TASK-001-marketplace-mvp
Run: RUN-2026-09-02-marketplace-002

Summary:
Test suite for the storefront (14 tests, 101 assertions).

Changes:
- tests/Feature/PagesTest.php: pages render, shared nav, EN default, ES toggle
  persists and returns, unsupported locale ignored, pricing tiers, enterprise
  content, no auth/cart/checkout/vendor routes
- tests/Feature/ContactFormTest.php (RefreshDatabase, in-memory sqlite):
  persistence + redirect, tier acceptance, validation errors + old input,
  Spanish validation messages, message bounds
- Removed scaffold placeholder tests; PHPUnit 12 attributes (#[Test]) used

Verification:
- php artisan test: 14 passed (101 assertions): PASS

## Commit — 2e14471

Date: 2026-09-02
Branch: feature/TASK-001-marketplace-mvp
Run: RUN-2026-09-02-marketplace-002

Summary:
Delivery polish: project README and production env defaults.

Changes:
- README.md: description, quickstart, page map, tests, design pointer,
  agent-memory conventions
- .env / .env.example: APP_ENV=production, APP_DEBUG=false

Verification:
- Fresh sqlite file + migrate + serve with production env: all routes 200: PASS
- php artisan test after flip: 14 passed: PASS
