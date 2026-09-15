# Frontend — Datum redesign (TASK-013) and the mockup-gap ledger

This storefront now runs design system v3 **"Datum"** (ADR-015): the light
sage Material-3 tonal palette, Epilogue/Manrope/Space Grotesk/IBM Plex Mono
type system, sharp 2px chip geometry, 8px cards, and the near-black/gold
accent pair shared with the Core dashboards. Signal v2 (dark, ADR-014) is
superseded but its records remain untouched, as always.

The redesign is **CSS-only**. Not a single Blade view, controller, route,
test, or line of `public/js/app.js` changed — the anime.js motion layer
keeps working because its runtime contract (the `--data / --data-solid /
--data-tint / --surface / --border-strong / --accent-soft` tokens and the
`.is-revealed / .is-new / .is-hit / .is-on` state classes) is preserved
verbatim in the new stylesheet. Bilingual EN/ES copy is untouched
(content audit: 86/86 items present).

## Marketplace layer (TASK-014, ADR-018)

The storefront is now presented as a **catalog** instead of a single
product page — same Datum palette, same copy, new structure:

| Page | Marketplace pattern |
|---|---|
| All | Dark utility bar (audience + EN/ES), topbar with a **Quote list** button + count badge, four-column footer with a catalog column |
| `/` | Featured dark hero panel (the ledger, tilted), a ticker of the event labels that appear on the site, category tiles, a package **listing shelf** with spec chips (readers/cards/applications), the package widget band, application **module cards** ("Included in" badges from the tier lists), a scrollable **use-case shelf** with receipt-style event records, the live demos, the problem section, audience tiles |
| `/pricing` | Breadcrumb hero, package listings (`#tier-starter/campus/enterprise`), a **comparison matrix** whose every cell is an existing feature line |
| `/product` | Product-detail layout: gallery stage (card → reader → stored event) with station thumbnails, sticky **buy box** with a package picker that GETs `/contact?tier=…` (works without JS), sticky sub-nav |
| `/enterprise` | Dark hero with the "includes" list as a buy box, use cases as catalog cards |
| `/contact` | Stepper built from the existing "what happens next" titles, form + order-summary aside |

**No new product facts.** `lang/*/store.php` holds only navigation and
control labels ("Compare packages", "Add to quote"…). Every package,
price model, feature, application and use case shown comes from the
existing `landing/product/pricing/enterprise/contact` files; a
render-diff of every pre-existing lang string (EN and ES) against the
new pages found all of them still present. Two presentational
derivations, both from already-published copy: the spec chips reuse the
landing widget's numbers (`1/200/1`, `2–10/2,000/3`, `∞/∞/3+`), and the
"Included in" badges read the tier lists (Starter = attendance; Campus
adds PAE + recycling; Enterprise's published "3+" applications).

**Quote list (`public/js/store.js`).** A per-visitor list in
`localStorage` (guarded; in-memory fallback). It is *not a cart*: it has
no prices, nothing is bought, and it says so in its own copy. Its only
output is the existing contact form — a prefilled message listing the
items, and the package preselected when exactly one is in the list.
Nothing new reaches the server (ADR-013 holds). The add buttons ship
`hidden` and are revealed by JS, so no-JS visitors see no dead controls;
the header button is a plain link to `/contact` without JS. `store.js`
runs under reduced motion (it is interaction, not animation). The server
also honours `?tier=` on `/contact` (unknown values ignored; `old()` wins).

Motion contract unchanged: every `app.js` hook (`#demo-*`,
`#package-widget`, `.ledger*`, `.tap-*`, `.hero-*`) is still rendered;
the ledger tilt lives on a `.ledger-tilt` wrapper because
`heroEntrance()` writes an inline transform on `.ledger`.

New tokens (`:root`): `--ink-inverse-muted/-faint`, `--line-inverse`,
`--wash-inverse`, `--glow-data/-gold`, `--glass`, `--scrim`, `--panel`,
`--shadow-deep` — all mixes of existing Datum hues. Nine pairs were
added to `scripts/contrast_audit.py` (`--ink-inverse-faint` was raised
from 0.52 to 0.58 to clear AA).

## Design reference (quick facts for future work)

- Ground `#f6fbed` sage; text `#181d15`; action near-black `#0e0f0e`;
  eco/points gold `#ffdf93`/`#ebc254` (ink `#241a00`, gold text ink
  `#594400`); error `#ba1a1a`; data-green family `#416e4a / #2f5238 /
  #35573b` for event labels and live states; inverse footer `#2d3229`.
- Type: Epilogue (display/headlines), Manrope (body), Space Grotesk
  (labels/chips), IBM Plex Mono (events/data). All self-hosted —
  `fonts.css` + `public/fonts/`, zero runtime external requests.
- Geometry: radius 2/4/8px (chips/inner/cards), shell 1140px, topbar 68px
  sticky + backdrop blur, breakpoints 940px/620px (keyed to the same
  mediaQueries in `app.js`).
- Tokens live in ONE place (`app.css :root`). Components reference
  aliases only; WCAG pairs are audited by `scripts/contrast_audit.py`
  (37 pairs, all AA as of this run).
- Known cosmetic residue: the JS `flashRow` teal wash (rgba(128,179,167))
  is hard-coded inside `app.js` and lands as a soft green flash on the
  ledger's newest row — harmonious on sage, documented here rather than
  touching the frozen motion layer.

## The gap ledger — parts that need functionality which does not exist

The owner's directive family for these redesigns says: build the design,
keep every real function, and **document the aspirational parts instead
of faking them**. Below is every storefront element a design like this
invites, that has no functionality behind it today, why, and what
building it would take. Nothing in this list is a dead button in the UI —
where an element would have required missing functionality, the UI either
omits it or states the truth (see the honesty floor notes).

### C — Commerce (the literal "marketplace" gap)

- **C1 — Cart.** *(TASK-014 note: a client-side **quote list** now exists — no prices, no checkout, its only output is a prefilled `/contact` request; it is not this gap closed.)* No cart exists anywhere; the pricing page's tier CTAs
  route to the contact form ("Request quote / Get started"), not a cart.
  *Why absent:* a single-vendor, tiered-package storefront that sells by
  conversation (`.agent/PROJECT.md` explicit non-goal: no cart/checkout).
  *Needs:* a cart data layer (session or client-side), cart UI, and a
  pricing model that is per-item rather than per-tier.
- **C2 — Checkout / payment processing.** No Stripe/PSP integration, no
  payment tokens, no billing addresses. *Why:* explicit non-goal —
  packages are quoted individually for schools/enterprises. *Needs:* a
  PSP account + webhooks + a `payments` domain (the Core platform has
  none either; it would be net-new across both repos).
- **C3 — Self-serve tier purchase.** The Campus/Enterprise tiers cannot
  be bought without a human. *Needs:* C1+C2 plus provisioning: today
  "deploying a package" means the platform team configures readers,
  cards and classes — there is no automated tenant provisioning to hook
  a purchase to.

### K — Contact / leads

- **K1 — Persisted leads.** Form submissions are validated, logged to
  the application log, and gone (ADR-013: stateless, no database).
  *Needs:* a persistence layer (DB table or external CRM) — a direct
  ADR-013 reversal, decision owner-level, not engineering-level.
- **K2 — Email notification on submission.** No mail transport is
  configured; nobody is emailed when the form is submitted. *Needs:*
  SMTP/provider credentials + a Mailable + a queue (a queue needs the
  jobs table → also touches statelessness).
- **K3 — CRM/lead pipeline integration.** No HubSpot/Salesforce/etc.
  *Needs:* an integration client + credentials; the thank-you page's
  "we'll reply" promise currently relies on humans reading logs.

### P — Social proof

- **P1 — Testimonials / customer quotes.** None anywhere. *Why:* no
  production customers exist yet; inventing quotes violates the
  content-preservation/honesty floor. *Needs:* real customers + their
  written permission; then a testimonials block (content + layout).
- **P2 — Customer logos / "trusted by" strip.** Same rule: zero real
  logos exist, so the strip does not render. *Needs:* real deployments
  + logo rights.
- **P3 — Case-study numbers ("40% less admin work").** No measured
  outcomes exist. *Needs:* a pilot with instrumented before/after
  metrics from the Core platform's own reports (leaderboard/deposit
  data would be the source), then copy.

### L — "Live" data

- **L1 — Real event feed.** The landing hero's event-log terminal and
  the ledger "LIVE" badge animate **scripted demo rows** — they are the
  product's pitch, not a feed. *Why:* this storefront deliberately has
  zero integration with the Core backend (two-app split, ARCH-001).
  *Needs:* a public, read-only, cross-tenant-safe events API on Core +
  an SSE/WebSocket consumer here; a privacy review first (card ids are
  personal data).
- **L2 — Live platform status.** No status page, no uptime chip.
  *Needs:* an external uptime monitor + a status route; inventing a
  green "All systems operational" badge with nothing behind it fails
  the honesty floor.

### D — Demo & onboarding

- **D1 — Interactive product demo / sandbox.** "Request a Demo" goes to
  the contact form. *Needs:* a hosted Core demo instance with seeded,
  anonymized data + either credentials-on-request or an open demo
  tenant. Nothing like this is deployed.
- **D2 — Demo scheduling (calendar).** No Calendly-style booking.
  *Needs:* a scheduling integration or a calendar backend; today a
  human replies by email (K2 gap aside) and arranges a time.

### A — Analytics & SEO

- **A1 — Web analytics.** No page-view/event analytics of any kind (no
  GA, no Plausible, nothing self-hosted). *Needs:* an analytics choice
  that fits the zero-CDN constraint (self-hosted or none) + consent
  handling. Currently the only signals are the contact form and deploy
  logs.
- **A2 — Sitemap / structured data.** No `sitemap.xml`, no JSON-LD
  product/organization schema. *Needs:* generation of both (simple to
  add — listed because it does not exist, not because it is hard).

### I — Internationalization

- **I1 — Locales beyond EN/ES.** The locale switch offers exactly EN and
  ES because those are the only `lang/` trees that exist. *Needs:* new
  translation files + any RTL/layout audit if a non-Latin locale joins.

### G — Governance pages

- **G1 — Privacy policy & terms pages.** The footer's legal line carries
  only the copyright; there is no /privacy or /terms. The contact form
  even says what happens to the data (honest: "logged, not databased").
  *Needs:* legal text from the operating company — engineering can add
  routes/views in minutes, but the content must come from humans with
  authority to write it.

## Honesty floor (what we did instead of faking)

- Pricing CTAs say what they do (route to contact) — no "Buy now"
  buttons that lead nowhere.
- The ledger terminal is labelled as a demo/pitch device in context
  ("LIVE" = the animation loops); no real-time claims are made.
- No testimonial/quote/logo/stat placeholders exist to be mistaken for
  content.
- The contact flow states where submissions go (log) in its own copy.
