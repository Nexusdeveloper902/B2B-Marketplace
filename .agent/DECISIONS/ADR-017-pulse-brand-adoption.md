# ADR-017 — The storefront is Pulse-branded

## Date
2026-09-12

## Context
The owner's productization pass names the product **Pulse** and
requires the real brand suite everywhere. The storefront still said
"Presence Platform" with a placeholder tap-tile wordmark, a placeholder
favicon.svg, and no PWA/social metadata. Its visual system ("Datum"
family, ADR-002/014/015) stays.

## Decision
- Wordmark (header + footer) = the REAL Pulse mark
  (`public/brand/mark-96.png`, derived from B2B-Logo-Suite; the dark
  footer uses the cream mono variant `mark-cream-96.png`) + the typed
  name.
- Head ships the favicon/ICO/PWA icon set, `manifest.webmanifest`,
  theme-color #E8EDDF, og/twitter metadata and `brand/og-image.png`.
- Copy: "Pulse" in lang common/landing/product/contact (EN+ES),
  READMEs, APP_NAME "Pulse Marketplace" (config default, .env,
  .env.example). Domain terms ("presence-event") are not brand and
  stay.

## Alternatives Considered
- Keep Presence Platform branding — rejected (owner directive).
- Vector-trace an SVG favicon — rejected (distortion risk; raster set
  is the authentic asset).

## Consequences
- PagesTest pins "Pulse" on every page; the suite is green (20/20).
- The footer proves the dark-surface rule: mono/cream mark variant on
  dark, ink/gold mark on light.

## Status
ACTIVE
