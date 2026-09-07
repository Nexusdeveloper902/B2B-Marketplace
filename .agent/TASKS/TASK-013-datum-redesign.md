# TASK-013: Apply the Core "Datum" redesign to the storefront (CSS-only)

## Date
2026-09-07

## Objective
Port the design system the Core dashboards just received ("Datum",
TASK-026 / ADR-036 there) onto this storefront, superseding the Signal
v2 dark theme — while keeping 100% of the functionality intact
(including the untouched anime.js motion layer) and documenting, in a
gap ledger, every part of the design that would need functionality
which does not exist yet.

Owner directive: "now apply the same refactor, same protocol" (+ PAT
supplied for push), with the standing instruction family: keep
functionality intact; document aspirational parts instead of faking
them.

## Requirements
- Datum family: same M3 sage palette values, type ramp, font stack,
  radius ladder, elevation model as Core (ADR-015).
- CSS-only implementation: no Blade/controller/route/test/JS changes.
- Motion-layer runtime contract preserved: --data / --data-solid /
  --data-tint / --surface / --border-strong / --accent-soft tokens and
  .is-revealed / .is-new / .is-hit / .is-on state classes must exist.
- Bilingual parity untouched (ADR-003 binding); content preservation
  86/86 (scripts/content_audit.py).
- WCAG AA on every introduced text/background pairing
  (scripts/contrast_audit.py, PAIRS re-pinned to Datum).
- Self-hosted fonts only (zero runtime external requests).
- Gap ledger: docs/FRONTEND.md + docs/FRONTEND.es.md, honesty floor
  (no fake data, no dead buttons).
- Append-only .agent records; push to origin with the supplied PAT
  (per-command env var, never persisted).

## Constraints
- Statelessness (ADR-013) untouchable — the redesign introduces no
  backend surface at all.
- Tokens centralized in ONE place: app.css :root (TASK-012 convention,
  reaffirmed by ADR-015).
- The anime.js bundle and app.js are frozen: any motion-layer change is
  out of scope by the "functionality intact" directive.

## Acceptance Criteria
- php artisan test: 17 tests / 120 assertions (identical to baseline).
- Contrast audit: ALL PAIRS PASS on the Datum set.
- Content audit: 86/86 items present.
- Browser verification: fonts loaded, motion demos fire, locale toggle
  and contact flow work, zero console errors, mobile 390px sound.
- VLM visual review: no defects on captured pages.
- main pushed to origin; remote == local verified via GitHub API.

## Result
COMPLETED — see RUN-2026-09-07-marketplace-015.

## Notes for future agents
- The stylesheet's public API now includes the motion contract tokens
  listed above; do not remove or rename them (ADR-015).
- Gap ledger codes: C/K/P/L/D/A/I/G (docs/FRONTEND.md). Owner-priority
  candidates if any get built: K1 (lead persistence — needs an ADR-013
  reversal decision), L1 (real event feed — needs a Core public API),
  G1 (legal pages — needs owner-authored content).
