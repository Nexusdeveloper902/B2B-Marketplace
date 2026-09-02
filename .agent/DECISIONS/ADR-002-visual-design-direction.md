# ADR-002: Visual design direction — "The Event Ledger"

## Date
2026-09-02

## Context
The client explicitly rejected default-template / AI-generated aesthetics
(cream+terracotta or near-black+neon palettes, uniform SaaS-card kits, ALL-CAPS
eyebrow labels, middle-dot metadata, arrow-suffixed CTAs, scattered scroll-triggered
fade animations, generic gradient-blob heroes). The design must feel deliberately
made for THIS product: physical presence-event infrastructure (NFC cards, readers,
timestamped events) sold to schools and enterprises.

## Decision
Design direction: **"The Event Ledger"** — the storefront's visual grammar is built
from the product's own core artifact, the timestamped event record. The site reads
like a well-set institutional ledger / spec sheet: cool paper ground, hairline rules
instead of floating cards, tabular data set in mono, a single signal-green "tap
moment" for emphasis.

### Palette (named, in `public/css/app.css` as custom properties)
- `--paper  #F3F4F0` — cool porcelain page ground (green-grey cast, NOT cream)
- `--surface #FFFFFF` — white content surfaces (tables, panels, form fields)
- `--ink  #101D18` — deep green-black: headings and body text
- `--pine #0A5C38` — primary brand green: CTAs, links, rules, tier emphasis
- `--go  #1D9E5F` — signal "go-light" green: reserved for the tap moment,
  live status dots, and form success (used sparingly)
- `--steel #53615A` — secondary text
- `--line #D7DCD5` — hairline rules and borders
- `--wash #E9EEE9` — tinted background for alternating sections

### Typefaces (self-hosted woff2 in `public/fonts/`, no CDN dependency)
- Headings: **Space Grotesk** 600/700 — geometric grotesque with an
  instrument-panel character; tight, confident display voice.
- Body: **IBM Plex Sans** 400/500/600 — designed for institutional/technical
  documentation; highly legible at small sizes.
- Data: **IBM Plex Mono** 400/500 — used ONLY where content is literally event
  data (timestamps, card IDs, event labels, reader IDs). This is a functional
  choice (the product's artifact IS a timestamped record), not decoration.

### Layout concept
- Slim top bar with hairline bottom border: wordmark left, nav center-right,
  EN/ES locale toggle, one solid-pine CTA. Mobile: nav collapses to a plain
  details-based disclosure menu (no JS framework needed).
- Sections separated by full-width hairlines on the paper ground; generous
  whitespace; NO uniform rounded-card kit, no soft box-shadows, no gradient washes.
- Hero (the single bold moment): asymmetric split. Left: left-aligned headline,
  pitch, CTAs. Right: a "live ledger" panel — a rendered event log where a card
  taps a reader (one orchestrated CSS animation loop: card slides in, go-light
  blinks, a new event row appends with a fresh timestamp). This is the ONLY
  scroll-independent animation; the rest of the site is static with restrained
  color/underline hover transitions and visible focus rings.
- Pricing: three columns divided by vertical hairlines — a spec table, not
  floating cards; the Campus tier carries a pine header band for emphasis.
- Product page: Card → Reader → Backend → Dashboards as connected ruled blocks
  with mono payload annotations; event-anatomy spec table.
- Enterprise page: labeled-event anatomy (who / when / where / what) plus example
  event rows for different use cases (shift check-in, zone access, asset tracking).

### One-sentence rationale
The product sells trustworthy records of physical taps, so the design makes the
product's actual artifact — the timestamped, labeled event row — the hero of the
page, using an institutional ledger/spec-sheet grammar that no generic B2B SaaS
template shares.

## Alternatives Considered
- Cream + terracotta editorial look — explicitly banned (AI-slop tell).
- Near-black + neon accent — explicitly banned; also wrong trust register for schools.
- A "hardware tech" dark theme with glow effects — same problem, rejected.
- Tailwind CDN + default component look — would drift back to the SaaS-card kit.

## Reasoning
Green is thematically load-bearing: it is both the institutional "records/trust"
color for schools and the go-light color of a successful card tap (and nods to the
recycling-incentive application). The ledger grammar (rules, tabular rows, mono
timestamps) is derived from the product's data shape rather than from a template.

## Consequences
- A future agent MUST NOT revert to cream/terracotta, dark+neon, rounded-card kits,
  eyebrow labels, middot metadata, arrow CTAs, or scattered scroll animations.
- Keep exactly one orchestrated motion moment (the hero tap loop) unless a
  deliberate new ADR supersedes this one.
- Copy stays plain and concrete (facts like "1 reader, up to 200 cards"), in
  English and Spanish.
- Accessibility floor is binding: responsive to ~360px, visible keyboard focus
  states, WCAG-passing contrast for text.

## Status
ACTIVE

## Supersedes
None
