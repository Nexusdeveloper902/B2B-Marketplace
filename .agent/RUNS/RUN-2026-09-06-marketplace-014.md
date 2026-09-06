# RUN-2026-09-06-marketplace-014

## Task
TASK-012-visual-redesign

## Agent Role
Senior front-end/design engineer (redesign agent)

## Result
COMPLETED

## Push Status
PUSHED: main b77c994..410f24c and feature/TASK-012-visual-redesign
(638fde1) to origin, verified via the GitHub API (branches endpoint).
Remote main == local main == 410f24c.

## Summary
Executed the full visual redesign of the storefront per the TASK-012 brief:
four-hue dark token system (scarlet/muted-teal/shadow-grey/tiger-orange,
matching the client palette image), landing page rebuilt on the
animejs.com homepage pattern (hero + code chip + live event-log terminal,
bento grids with 7 live demos + SVG pipeline, interactive package widget,
scroll reveals), anime.js v4.5.0 integrated as a self-hosted ESM bundle,
all other pages re-themed via the shared stylesheet. Content preserved
1:1 (86/86 audit). All tests, lint, and browser verification pass.

## Changes Made
- public/css/app.css: full rewrite — 40 raw scale tokens + semantic
  aliases, dark theme, all component styles (every class in the Blade
  inventory covered), responsive 940/620 breakpoints, reduced-motion block,
  reveal hidden-states gated on .js-motion, CSS keyframe hero loop kept as
  no-JS/reduced-motion fallback (.js-motion disables it when JS drives).
- public/js/app.js (new): ES-module motion layer on anime.js v4.
  Root createScope (mediaQueries: compact/mobile), hero entrance, ledger
  timeline loop (card->reader, go-light, row cycling via chained
  callbacks), draggable tap demo (spring release), identify convergence
  loop (spring ease), timestamp stamp loop, report/meals onScroll
  count-ups (click to replay), attendance grid stagger-from-center loop,
  recycle randomized scatter, pipeline createDrawable line-draw +
  createMotionPath travelling dot, package widget tab animations,
  onScroll reveal system (class hand-off to CSS).
- public/js/vendor/anime.esm.min.js (new): anime.js v4.5.0 minified ESM
  bundle, self-hosted (no CDN).
- resources/views/pages/landing.blade.php: rebuilt on the animejs.com
  pattern; every original content item kept; demos + widget added.
- resources/views/layouts/app.blade.php: pre-paint motion-gate inline
  script (never flags under reduced-motion), module script, body
  data-page for page-keyed behaviour.
- lang/en/landing.php + lang/es/landing.php: new keys only (kicker, chip,
  demo labels/aria/hints, meals day letters, pipeline title, widget
  labels/note/cta) — bilingual parity maintained (ADR-003).
- Interior pages (product/pricing/enterprise/contact/thank-you): NO Blade
  changes; re-themed via shared CSS only.
- .agent: TASK-012, this run record, ADR-014 (supersedes ADR-002),
  OBS-018, content inventory file.

## Files Changed
- M public/css/app.css
- M resources/views/pages/landing.blade.php
- M resources/views/layouts/app.blade.php
- M lang/en/landing.php
- M lang/es/landing.php
- A public/js/app.js
- A public/js/vendor/anime.esm.min.js
- A .agent/TASKS/TASK-012-visual-redesign.md
- A .agent/DECISIONS/ADR-014-visual-redesign-v2-signal.md
- A .agent/OBSERVATIONS/OBS-018-cdp-fullpage-capture-artifact.md
- A .agent/OBSERVATIONS/CONTENT-INVENTORY-landing-2026-09-06.md
- A .agent/RUNS/RUN-2026-09-06-marketplace-014.md (this file)
- A .agent/STATE/SNAPSHOT-RUN-2026-09-06-marketplace-014.md

## Verification
- php artisan test: PASS — 17 tests / 120 assertions (baseline before
  changes: identical 17/120).
- Laravel Pint (--test) on all changed PHP/Blade files: PASS.
- Live serve smoke (php artisan serve): / /product /pricing /enterprise
  /contact /contact/thank-you all 200; /js/app.js and
  /js/vendor/anime.esm.min.js served 200.
- Browser (agent-browser, real input): console clean (no errors, no
  warnings after switching to v4.5.0's spring()); draggable card demo
  hit-detection works (is-hit + readout on drag onto reader); widget tab
  switch animates (Campus: 2-10 readers @55%, 2,000 cards, 3 apps);
  EN/ES locale toggle works end-to-end (ES hero/kicker/tags/widget copy
  verified in DOM); contact form submits -> redirect -> Spanish thank-you
  page; mobile 390px: mobile nav visible, bento cards full-width.
- Visual QA (VLM on scroll-stitched captures, OBS-018 explains why
  scroll-stitch was used): landing, product, pricing, enterprise,
  contact, thank-you all render cleanly; hero verified pixel-level
  (stdev 44.2, max 255).
- Content preservation audit (scripts/content_audit.py): 86/86 items
  present.
- WCAG contrast audit (scripts/contrast_audit.py): 36/36 pairs pass AA
  after restricting grey-400 to 950/900 grounds.

## Discoveries
- anime.js v4.5.0 deprecates createSpring() in favour of spring() —
  code uses spring(); spring release physics for createDraggable use the
  documented releaseStiffness/releaseDamping params.
- CDP captureBeyondViewport (Playwright fullPage) blanks this site's hero
  on http origins — tool artifact only; see OBS-018. Use scroll-stitched
  or viewport screenshots for visual verification.
- The static PHP toolchain had to be re-provisioned in this fresh sandbox
  (OBS-002/OBS-017 path: static-php.dev 8.4.23 + composer phar from
  GitHub releases; getcomposer.org download URLs were failing).

## Decisions
- ADR-014-visual-redesign-v2-signal (supersedes ADR-002).
- Self-host the anime.js ESM bundle rather than add a build pipeline
  (project is deliberately bundler-less; see ADR-014 alternatives).
- Tested copy strings (EN/ES headlines, pricing, enterprise assertions in
  PagesTest) kept as contiguous text nodes; the accent-phrase device is
  expressed via the mono kicker, code chip, and demo accents instead of
  colored spans inside the headline.
- No invented footer socials (no real URLs exist — content-preservation
  rule).

## Problems / Blockers
- None blocking. getcomposer.org was unreachable; composer phar fetched
  from GitHub releases instead.

## Remaining Work
- None for TASK-012. Follow-ups (optional, need human decision):
  - Purge the old APP_KEY from git history (pre-existing, §7.12 of
    TASK-011 documentation).
  - Consider locale-aware numeral formatting for the widget counters if
    non-en/es locales are ever added.
  - The `.hero-chip` copy interaction uses navigator.clipboard (https or
    localhost only); degrades silently to no-op elsewhere.

## Next Agent Notes
- Do NOT use agent-browser `screenshot --full` on this site (OBS-018);
  use /home/z/my-project/scripts/capture_page.sh (scroll-stitch) or
  viewport screenshots.
- Toolchain re-provision steps are in OBS-002/OBS-017 (php: static-php.dev
  common bundle; composer: GitHub releases latest phar).
- anime.js upgrades: npm i animejs, copy dist/bundles/anime.esm.min.js
  over public/js/vendor/, adjust imports in public/js/app.js if the API
  changes (v4.5: spring() not createSpring()).
- CSS tokens: raw scales + semantic aliases are the ONLY color source;
  keep new components on the aliases (contrast rules in the CSS header
  comment, audit in scripts/contrast_audit.py).
