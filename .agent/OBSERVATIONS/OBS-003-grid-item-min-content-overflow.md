# OBS-003: Grid-item min-content overflow — the mobile ledger panel bug pattern

## Date
2026-09-02

## Observation
The hero ledger panel (`.ledger`, a CSS grid item) overflowed its 1fr track on
mobile: `document.documentElement.scrollWidth` was 462px on a 360px viewport.
Root cause: grid items default to `min-width: auto`, and the panel's dark
caption bar contains monospace text ("Registro de eventos — Colegio Riverside"
plus "registrando") whose unbreakable min-content width was 442px. The inner
flex item already had `min-width: 0` + ellipsis, but that does NOT reduce the
GRID item's own min-content size. The page-level symptom was horizontal
scrolling on every page that rendered the hero.

## Evidence
- Headless-browser DOM measurement: `.ledger` 442px inside a 320px track;
  hiding the table and tap visual did not change the width (isolating the
  caption bar as the contributor).
- Fix (`min-width: 0` on `.ledger`) verified: bodyScrollW == clientW at
  360px/390px/1440px on all six routes.

## Impact
- Any future layout that puts wide monospace/nowrap content inside a grid or
  flex child MUST set `min-width: 0` (or `overflow: hidden`) on the item, or the
  page will silently gain horizontal scroll on narrow screens.
- VLM screenshot review flagged "table too wide", but the real cause was the
  caption bar, not the table — measure the DOM before trusting visual reviews.

## Related Task
TASK-001-marketplace-mvp

## Status
CONFIRMED
