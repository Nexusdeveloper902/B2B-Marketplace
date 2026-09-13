# STATE SNAPSHOT — RUN-2026-09-13-marketplace-017

## Overall Status
Landing hero fix + translations + branded 404 DONE (uncommitted).

## Completed
- Hero tap-visual centered (reader inset 80px at 1366); distance()
  measures the real gap.
- Landing demo translations EN/ES; branded localized 404 view + CSS +
  keys.

## Known Problems
- None new.

## Important Current Facts
- Suite 20/20 (PagesTest pins "Pulse").
- Locale route: /lang/{locale}. Branded 404 exists now.

## Follow-up (same day)
- The REAL "Touch panel" bug: `.demo-tap-stage` overflowed the demo
  frame by 47px under default content-box sizing (no global border-box
  in the storefront) — the GATE-A reader stuck 23px out, clipped. Fixed
  with the global `*, *::before, *::after { box-sizing: border-box; }`
  reset.
- Sweep fallout fixed: /product @ 390px overflow (closing grid blowout
  → minmax(0,1fr); nowrap CTA + long ES label → wraps in
  .closing-actions).
- Overflow sweep: all 6 URLs × 1366/900/390 = 0. Suite 20/20.
