# ADR-014: Visual redesign v2 "Signal" — dark animejs.com-pattern storefront

## Date
2026-09-06

## Context
TASK-012 (2026-09-06) requested a full visual redesign of the storefront
following the animejs.com v4 homepage pattern: dark ground, four-hue token
system (scarlet / muted-teal / shadow-grey / tiger-orange from the client
palette image), a feature bento grid where every capability is shown with a
live self-contained animation demo, scroll-triggered reveals, an interactive
metrics/breakdown widget, and anime.js v4 as the motion engine. This
supersedes the "Event Ledger" direction recorded in ADR-002 (light paper
theme, single motion moment, no scroll animations). ADR-002's constraints
(light theme, one-motion-moment cap, no rounded cards) directly conflict
with the new brief and are therefore replaced by this decision.

## Decision
Design system v2 "Signal":
- Ground: shadow-grey 950 (#121013); raised bands grey 900; surfaces grey
  800; hairline borders grey 700/800. Dark theme as the brief specifies.
- Scarlet 500/400 = primary accent (CTAs on 600 for AA, accent words on
  400/500-large). Muted-teal 400/500 = secondary/data accent: event labels,
  live states, focus fields, success. Tiger-orange 400/500 = tertiary,
  sparing (kicker prompt, one widget row, recycle demo shapes).
- All 40 raw scale tokens live as CSS custom properties on `:root` in
  `public/css/app.css` + semantic aliases (--bg, --surface, --text, --accent,
  --data, --spare...); components reference aliases only. Verified WCAG AA
  (scripts/contrast_audit.py, 36 pairs pass; grey-400 restricted to 950/900
  grounds).
- Type unchanged: Space Grotesk / IBM Plex Sans / IBM Plex Mono (self-hosted).
- Motion: anime.js v4.5.0, self-hosted ESM bundle at
  `public/js/vendor/anime.esm.min.js` (no CDN, consistent with OBS-006),
  driven by `public/js/app.js` (ES module). One root `createScope` with
  responsive mediaQueries; `onScroll` for reveals; `createTimeline` loops;
  `createDraggable` + spring release for the tap demo; `createDrawable` +
  `draw` for the pipeline SVG line-draw; `createMotionPath` for the
  travelling tap; `stagger` for grids/bento; `utils.random` for the recycle
  scatter. `prefers-reduced-motion`: a pre-paint inline gate script never
  adds `.js-motion`, the module exits early, and CSS static states apply.
- Landing rebuilt on the animejs.com pattern: hero (kicker + large headline
  + quickstart-style event code chip with copy-on-click + live event-log
  terminal), problem section, 4-step bento with live demos, 3-application
  bento + SVG pipeline card, interactive package-breakdown widget (real
  published tier numbers), audience rows, closing band.
- Interior pages (product/pricing/enterprise/contact/thank-you) inherit the
  theme via the shared stylesheet; their Blade markup is unchanged.

## Alternatives Considered
- Keep ADR-002 "Event Ledger" light theme — conflicts with the new brief.
- CDN import of anime.js — rejected (OBS-006 mixed-content lesson; the site
  self-hosts fonts for the same reason).
- Adding Vite/npm build pipeline for tree-shaking anime.js — rejected: the
  project deliberately has no Node build tooling; the single 116 KB minified
  ESM bundle (~30 KB gzipped) is the standard bundler-less integration path.
- `createTimer`/`morphTo` — not used (no content to morph; loops are
  timelines). `createSpring` is deprecated in v4.5.0 in favour of `spring()`
  — code uses `spring()`; draggable spring release uses the documented
  `releaseStiffness`/`releaseDamping` params.

## Reasoning
The brief's palette + structure (bento demos, scroll reveals, metrics
widget) is the animejs.com pattern; the product's own artifact (the
timestamped event record) remains the hero demo, now animated with the same
library the pattern references. All motion degrades to complete, readable
static states under reduced-motion / no-JS.

## Consequences
- Content is preserved 1:1 (86/86 audit, see RUN-014); copy strings asserted
  by PagesTest remain contiguous text nodes (no accent spans inside the
  tested headlines).
- Footer has no socials row (no real social URLs exist for the project;
  inventing them would violate content preservation).
- anime.js ships as a static vendor file: future upgrades are file swaps;
  `npm i animejs` + copy `dist/bundles/anime.esm.min.js`.
- The old CSS keyframe hero loop is retained as a no-JS/reduced-motion
  fallback (`.js-motion` disables it when the JS engine drives the loop).
- ADR-002 is superseded but remains in history (append-only).
- Known testing-environment quirk (not a product bug): Chromium CDP
  `captureBeyondViewport` (Playwright fullPage screenshots) renders the hero
  region blank on http origins while normal compositing renders it
  correctly — verified via viewport/scroll-stitched screenshots, DOM
  geometry, and computed styles. See OBS-018.

## Status
ACTIVE

## Supersedes
ADR-002-visual-design-direction.md
