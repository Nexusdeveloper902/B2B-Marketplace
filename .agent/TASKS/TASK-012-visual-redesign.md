# TASK-012: Full visual redesign — dark "animejs.com-pattern" storefront

## Date
2026-09-06

## Objective
Execute a full visual redesign of the storefront following the redesign
brief supplied with this task:

1. New design system: four-hue token palette (scarlet / muted-teal /
   shadow-grey / tiger-orange) as CSS custom properties, dark ground
   (shadow-grey 900/950), scarlet as primary accent, teal secondary,
   orange tertiary/sparing.
2. Rebuild the landing page on the structural/motion pattern of
   animejs.com v4 site: hero with accent phrase + quickstart code chip,
   feature bento grid with LIVE self-contained animation demos + tiny
   code snippets, scroll-triggered reveals, a metrics/breakdown widget
   backed by real project numbers, multi-column footer.
3. Integrate anime.js v4 (ESM, import-based) for all motion: timeline,
   stagger, onScroll reveals, createDraggable + spring, SVG line-draw
   (createDrawable), motion path, responsive variant. Respect
   prefers-reduced-motion.
4. Content preservation is non-negotiable: every fact/feature/link/stat
   from the pre-redesign inventory must remain (rewording allowed,
   deletion not). All links, the locale toggle, and the contact form
   must keep working.

## Requirements
- Tokens centralized in ONE place (public/css/app.css :root), referenced
  everywhere; no hardcoded hex in component rules.
- WCAG AA contrast for every text/background pairing introduced
  (4.5:1 body, 3:1 large text).
- anime.js v4 only (npm i animejs). Import only used sub-modules per
  file. No CDN (self-hosted, consistent with fonts decision, OBS-006).
- Bilingual parity: every new UI string added to lang/en AND lang/es
  simultaneously (ADR-003).
- Keep stateless architecture (ADR-013) — no DB, no backend changes.

## Constraints
- Tests currently assert exact copy strings (EN + ES hero headlines,
  pricing tier strings, enterprise event names). Those strings must
  remain present as contiguous text in rendered output.
- ADR-002 "Event Ledger" direction is superseded by this task via a new
  ADR (append-only: ADR-002 itself is NOT modified).
- No bundler exists in the project (no Vite/npm pipeline). anime.js is
  delivered as a self-hosted static ESM file under public/js/.

## Acceptance Criteria
- php artisan test passes (17 tests / 120 assertions minimum).
- Laravel Pint passes on changed PHP files.
- All 5 pages render with the new theme; nav, locale toggle, contact
  form, footer links functional.
- Landing page implements: hero + code chip + bento cards with live
  demos + scroll reveals + metrics widget; reduced-motion respected.
- Content inventory check: every item still present.

## Explicitly Out of Scope
- Rewriting business logic (contact controller, locale middleware).
- Any database reintroduction.
- SEO / locale-prefixed URLs.
