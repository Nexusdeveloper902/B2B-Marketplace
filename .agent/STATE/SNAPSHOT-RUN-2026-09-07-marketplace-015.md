# STATE SNAPSHOT — RUN-2026-09-07-marketplace-015

## Overall Status
HEALTHY. TASK-013 complete and verified: design system v3 "Datum"
(ADR-015, supersedes Signal v2/ADR-014) live on all six pages, CSS-only
implementation (zero Blade/PHP/JS changes), anime.js motion layer intact
and verified live, gap ledger shipped (docs/FRONTEND.md + .es.md).
17 tests / 120 assertions PASS; content 86/86; contrast 37/37 AA;
browser + VLM clean; pushed to origin.

## Completed
- TASK-013-datum-redesign (this run) — full change list and
  verification matrix in RUN-2026-09-07-marketplace-015.
- All prior tasks TASK-001..TASK-012 remain completed (nothing outside
  CSS/fonts/docs/records was touched).

## In Progress
- Nothing.

## Blocked
- Nothing.

## Known Problems
- CDP fullPage screenshot artifact (OBS-018) — tool-only, unchanged.
- Historical APP_KEY in git history (pre-existing, unused).
- Pre-existing pint findings on config/app.php + config/database.php
  (exist on the parent commit; untouched per unrelated-change rule).
- app.js flashRow hard-codes a teal rgba row-flash — accepted residue,
  documented in FRONTEND.md (frozen motion layer).

## Important Current Facts
- Design system: "Datum" v3 (ADR-015) — M3 sage palette, Epilogue/
  Manrope/Space Grotesk/IBM Plex Mono, 2/4/8px radii, shell 1140px,
  topbar 68px sticky blur, breakpoints 940/620. Tokens in ONE place:
  public/css/app.css :root (motion-contract tokens are public API).
- Motion layer untouched: public/js/app.js + self-hosted anime.js
  v4.5.0; its contract tokens (--data #416e4a, --data-solid, --data-tint,
  --surface, --border-strong, --accent-soft) and state classes
  (.is-revealed/.is-new/.is-hit/.is-on) resolve live.
- Statelessness (ADR-013) untouched; bilingual parity untouched
  (ADR-003); tests still assert the same copy strings.
- WCAG: 37 pairs AA (scripts/contrast_audit.py, re-pinned to Datum);
  --text-meta is #666861 (outline ink darkened — outline itself stays
  #767873 for borders).
- Sandbox facts: backgrounded servers die between tool calls (run
  server+checks in one invocation); content_audit.py targets port 8099;
  hermetic PHP reusable from B2B-Core/.tools/php.
- Sibling repos (audited green this session): B2B-Core d3a94c0 (CI 3/3:
  275/1-skip/4442, e2e 33/33), B2B-Firmware d657265 (native 90/90, 3
  device envs SUCCESS — owner-side bench commit), ESP32-CAM-CV 5c62dc1
  (pytest 114/1-skip, build ±secrets SUCCESS).

## Current Main Commit
Post-run push: TASK-013 merge on main (see git log; local == origin,
verified via GitHub API).

## Current Main Status
BUILDABLE — 17 tests / 120 assertions PASS; Pint clean on changed
scope (no PHP changed); 6 pages 200 on live serve; motion + locale +
contact E2E verified in browser; VLM: all pages CLEAN.

## Active Branches
- main (TASK-013 merged)
- feature/TASK-012-visual-redesign (kept for reference, untouched)

## Owner Decisions Waiting (gap ledger)
- K1 lead persistence (ADR-013 reversal), L1 real event feed (Core
  public API + privacy review), G1 legal pages (owner-authored text).
  Full list: docs/FRONTEND.md.
