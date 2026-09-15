# ADR-018 — The storefront is presented as a catalog (marketplace layer)

## Date
2026-09-15

## Context
The owner: "keep the same info and colors, but make it more visually
astonishing and feel like a real marketplace (don't just hallucinate)
instead of a random product page". ADR-015 made the site Datum-styled
but structurally it still read as one long product brochure. The
honesty floor (FRONTEND.md) and ADR-013 (stateless, no cart/checkout)
still bind.

## Decision
Restructure the Blade views into marketplace idioms — utility bar,
featured hero, category tiles, package listings with spec chips, module
cards with "Included in" badges, scrollable use-case shelf, comparison
matrix, product-detail page with a buy box, quote-request stepper — on
the unchanged Datum palette (only alpha mixes of existing hues are new
tokens). Copy stays in the existing lang files; a new `store.php` holds
UI labels only. A client-side **quote list** (localStorage, guarded)
replaces the idea of a cart: its only output is a prefilled `/contact`
request, and `/contact` accepts `?tier=` to preselect a package.

## Alternatives Considered
- CSS-only again (ADR-015 style) — rejected: "feel like a marketplace"
  is a structural ask (catalog, comparison, buy box), not a re-skin.
- A real cart/checkout — rejected: ADR-013 + PROJECT.md non-goals;
  packages are quoted by humans.
- Server-side quote list (session) — rejected: adds server state for a
  convenience; localStorage keeps the app stateless.
- Invented marketplace furniture (ratings, "best seller", customer
  logos, prices) — rejected: no such data exists (gap ledger P1–P3).

## Consequences
- Motion-layer hooks preserved; `public/js/app.js` untouched.
- New `public/js/store.js` (interaction; runs under reduced motion).
- Tests 20 → 28 (StorefrontCatalogTest); contrast audit +9 pairs, all AA.
- Gap ledger C1 annotated (quote list ≠ cart).

## Status
ACTIVE (builds on ADR-015 / ADR-017; supersedes nothing)
