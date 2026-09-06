# OBS-018: CDP captureBeyondViewport blanks the hero region on http origins (tool artifact, not a product bug)

## Date
2026-09-06

## Observation
While visually verifying the TASK-012 redesign with agent-browser
(Playwright/CDP), `screenshot --full` (Chromium's `captureBeyondViewport`
mode) rendered the landing hero region (both text and ledger columns) as
blank dark space, while every other section rendered normally.

## Evidence (matrix)
- Viewport screenshots at 1440x900 AND 1440x5400: hero renders correctly
  (pixel stdev ~44, text max 255).
- Live DOM: hero h1/ledger bounding boxes and computed styles correct
  (opacity 1, visible, correct geometry).
- `--full` capture on the live http page: blank hero regardless of:
  topbar hidden, hero::before disabled, inline anime styles removed,
  `.js-motion` class removed, rAF halted, other sections hidden,
  ledger removed — i.e. independent of every suspected mechanism.
- The same rendered HTML saved to a file and re-served from `file://`
  (scripts re-injected): `--full` capture renders the hero correctly.
- Scroll-stitched capture (real compositing path, script
  `scripts/capture_page.sh`): hero renders perfectly; VLM design review of
  the stitched image confirms all sections well-formed.
- In `--full` mode the 72px sticky topbar also renders as a ~24px sliver —
  strong indicator of the known Chromium beyond-viewport compositing bug
  with sticky/animated content, not of a page defect.

## Impact
- No user-facing impact: real browsers never render via
  captureBeyondViewport; normal compositing is verified correct at multiple
  viewports.
- Future agents should NOT use `screenshot --full` to verify this site;
  use the scroll-stitch script or plain viewport screenshots.

## Related Task
TASK-012-visual-redesign

## Status
CONFIRMED (as a testing-tool artifact)
