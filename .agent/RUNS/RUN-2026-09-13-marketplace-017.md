# RUN-2026-09-13-marketplace-017 — Landing hero fix, translations, branded 404

Owner (chat, 2026-09-13): the "Touch/Toque" panel's gate reader sat too
far right (read as going off screen); translations incomplete in the
animation demos; a search bar needing Enter (Core-side, see this
workspace's Core RUN-2026-09-13-core-040).

## Changes
- Hero `tap-visual`: centered pair, bounded gap clamp, 28px insets
  (reader was pinned to the panel border by space-between).
- `ledgerLoop.distance()`: real offsetLeft-based gap (transform-free),
  floor 40px, stop-short margin 14px — layout-proof.
- Landing translations: `landing.demo.identify_join_prefix` /
  `identify_join_event` / `stamp_recorded` (EN+ES) wired into the view.
- Branded 404: `errors/404.blade.php` (app shell, `.nf-*` CSS,
  `common.not_found_*` EN/ES) — the storefront previously had NO error
  view.

## Verification
- Suite 20/20. Browser-proven: reader inset measured (80px from panel
  edge at 1366, no viewport overflow at 390-1366); ES landing shows
  "quién + dónde = un evento" / "registrado"; branded 404 screenshot
  (EN; ES keys verified in lang files).

## Status
DONE 2026-09-13 — uncommitted tree.

## Follow-up (same day) — the REAL "Touch panel" bug + global sizing reset

The owner corrected me: the broken panel was the **Tap/Toque demo card**
itself, not the hero. Root cause measured: `.demo-tap-stage` (width
100% + 48px horizontal padding) overflows the `#demo-tap` frame by 47px
under CSS's default content-box sizing — the GATE-A reader stuck 23px
outside the frame, clipped by overflow:hidden. The storefront
stylesheet had NO global `box-sizing: border-box` (only `.btn` carried
one individually).

- Fix: global `*, *::before, *::after { box-sizing: border-box; }` at
  the top of app.css (Core has had this from day one).
- Sweep fallout (caught by the post-change overflow sweep, not assumed):
  `/product @ 390px` had 26px of overflow — two causes: (a) the
  `.closing-in` grid `1fr` track blowing out on its items' min-content
  (fixed with `minmax(0, 1fr)` + `min-width: 0` on the text cell), and
  (b) `.btn { white-space: nowrap }` + the long ES closing CTA label
  ("Cómo funciona el seguimiento personalizado") — the closing band's
  button now wraps (`white-space: normal` scoped to `.closing-actions`).
- Verified: reader 25px INSIDE the frame; full overflow sweep
  (`/` `/product` `/pricing` `/enterprise` `/contact` `/no-existe` ×
  1366/900/390) = 0 everywhere. Suite 20/20.
