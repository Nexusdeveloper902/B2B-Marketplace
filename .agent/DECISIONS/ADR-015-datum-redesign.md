# ADR-015 — Visual redesign v3 "Datum": the storefront joins the Core design family

## Date
2026-09-07

## Context
The Core dashboards received the owner-directed "Datum" redesign
(TASK-026 there, ADR-036): a light sage Material-3 tonal system with
Epilogue/Manrope/Space Grotesk/IBM Plex Mono, replacing their Signal
dark theme. This storefront still ran Signal v2 (ADR-014) — dark
scarlet/teal/grey/orange — so the two public faces of the platform
visually diverged. The owner's directive ("apply the same refactor, same
protocol") asked for the same treatment here, with functionality kept
fully intact and any aspirational element documented rather than faked.

## Decision
Adopt design system v3 **"Datum"** for the storefront: the identical M3
sage tonal palette values, the identical type scale and font stack, the
identical radius ladder (2/4/8px) and elevation model as Core's Datum.
Storefront-specific semantic layer on top: a data-green family
(--data #416e4a / --data-strong #2f5238 / --data-solid #35573b /
--data-tint #d5e4d7) for event labels and live states (Core had no
equivalent need), and an inverse-surface footer/closing band.

The implementation is **CSS-only**: `public/css/app.css` fully
rewritten, `public/css/fonts.css` extended with the vendored
Epilogue/Manrope variable fonts (latin + latin-ext, copied from Core's
already-vetted font set). Zero changes to Blade views, controllers,
routes, tests, or `public/js/app.js`.

The anime.js motion layer's runtime contract is treated as a public API
of the stylesheet: the tokens it resolves at boot (--data, --data-solid,
--data-tint, --surface, --border-strong, --accent-soft) and the state
classes it toggles (.is-revealed, .is-new, .is-hit, .is-on) must exist
in every future stylesheet revision.

`scripts/contrast_audit.py` PAIRS are re-pinned to the Datum palette
(the audit tool tracks the live design system by convention).
The aspirational-element documentation lives in `docs/FRONTEND.md` +
`docs/FRONTEND.es.md` (gap ledger, honesty floor).

## Alternatives Considered
- Keep Signal (no change) — rejected: the owner explicitly asked for the
  same refactor; visual family unity across storefront and dashboards is
  now the owner's expressed direction.
- Port Core's tokens.css as a separate file — rejected: this repo's
  binding convention (TASK-012 spec, still valid) is tokens in ONE
  place, `app.css :root`; adding a second file would violate the
  repo's own rules for zero benefit at this size.
- Touch Blade views for new markup (like Core's TASK-026 did) —
  rejected: Core had owner-supplied mockups mandating new DOM; the
  storefront has no new mockups, so the existing semantic markup is
  already sufficient and a CSS-only change is the smallest correct
  change (protocol §1.4).

## Reasoning
- The marketplace's own records (RUN-014) proved the v2 architecture
  separates content (Blade/lang) from presentation (CSS) cleanly: the
  interior pages were re-themed in v2 with zero Blade changes. The same
  seam carries a full design-system swap.
- Content preservation is structural when no Blade file changes: the
  86/86 content audit and the 17 tests / 120 assertions pass unchanged.
- The motion layer is the only JS on the site and is frozen by the
  "functionality intact" constraint; its documented contract makes a
  CSS-only re-skin safe.

## Consequences
- The storefront and the Core dashboards now share the Datum family:
  same palette values, same type ramp, same geometry. Future Datum
  token changes should be applied to both `:root` blocks (documented in
  both repos' FRONTEND docs).
- Signal v2 lives only in git history and in ADR-014 (superseded,
  record intact).
- Known cosmetic residue, accepted and documented: `app.js` flashRow()
  hard-codes a teal rgba wash for the ledger row flash; on the sage
  ground it reads as a soft green flash. Fixing it would touch the
  frozen motion layer for no functional gain.
- WCAG AA verified on 37 token pairs by the re-pinned contrast audit;
  --text-meta was darkened from the M3 outline (#767873) to #666861
  because outline-as-text missed 4.5:1 on the sage ground (outline
  itself remains the border/UI token).

## Status
ACTIVE (supersedes ADR-014-visual-redesign-v2-signal.md, which remains
on record and unmodified)
