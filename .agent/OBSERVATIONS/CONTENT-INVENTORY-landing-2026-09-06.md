# CONTENT INVENTORY — landing page (/) — pre-redesign snapshot (2026-09-06)

Source of truth: resources/views/pages/landing.blade.php @ main 168c4ce +
lang/en/landing.php + lang/es/landing.php + partials (header/footer) +
lang/en|es/{nav,common}.php.

Every item below MUST be present after the redesign. Checked = verified. FINAL AUDIT 2026-09-06 (RUN-014, scripts/content_audit.py): 86/86 items present. Nothing dropped; nothing merged. Additions only: kicker, code chip, demo cards/tags/snippets, pipeline card, package widget (all new keys added to lang/en + lang/es in parity).

## Hero
- [x] H1 headline (EN: "Every card tap becomes a record you can trust." /
      ES: "Cada toque de tarjeta se convierte en un registro confiable.")
      — test-asserted exact string, must stay contiguous text
- [x] Pitch: "Presence Platform is presence-event infrastructure: one NFC
      card, one tap, one timestamped event. Attendance, meal service, and
      recycling incentives all read from the same stream."
- [x] CTA primary "See it in action" → /product  (ES: "Véalo en acción")
- [x] CTA secondary "Request a demo" → /contact (ES: "Solicitar una demo")
- [x] Meta title/description (landing.meta_title / meta_description)

## Event ledger demo (hero visual)
- [x] Panel title "Event log — Riverside School" + "recording" live label
- [x] Ledger columns: time / card / reader / event (localized)
- [x] Card visual "CARD 0441" + reader "GATE-A"
- [x] Event rows (data, untranslated — product data):
      07:58:12 0441 GATE-A attendance.in
      07:58:14 0087 GATE-A attendance.in
      08:02:47 0132 GATE-B attendance.in
      12:14:03 0441 PAE-1   meal.lunch
      15:41:09 0558 ECO-PT  recycle.drop
      16:22:41 0132 GATE-B  attendance.out
- [x] aria-labels (ledger.aria, tap_aria)

## Problem section
- [x] H2 "The record is the problem." (ES: "El problema es el registro.")
- [x] body_1 (hand counting: rolls, clickers, spreadsheets, memory)
- [x] body_2 (drift, audit challenges, days of reports, funding stakes)
- [x] costs_title "What weak records cost"
- [x] 3 cost list items (disputes / meal counts reconcile / incentives stall)

## Steps section ("From tap to report")
- [x] H2 + intro ("same four-part event every time")
- [x] 4 steps, each title+body: Tap / Identify / Timestamp / Report
      (facts: wall-mounted reader, no app/battery/pair; card ID + reader
      ID; immutable record; dashboards read same stream)

## Applications section ("Three applications, one event stream")
- [x] H2 + intro ("same architecture… different reports")
- [x] 3 apps with mono labels:
      attendance.in — Attendance
      meal.lunch — Meal tracking (PAE)
      recycle.drop — Recycling incentives
      + each body text

## Audience section ("Who it is for")
- [x] 3 rows with title+body+link:
      Small schools → "See the Starter package" → pricing
      Larger schools and campuses → "See the Campus package" → pricing
      Businesses and organizations → "See Enterprise" → enterprise

## Closing CTA
- [x] H2 "See what a tap can do." + body
- [x] CTA primary → /product, CTA secondary → /contact

## Shared chrome (all pages)
- [x] Header: wordmark Presence Platform → /; nav Product/Pricing/
      Enterprise/Contact; EN/ES toggle (GET /lang/{locale}); CTA button
      "Request a demo"; mobile details-menu
- [x] Footer: brand + note; "Site" col (4 links); "Get started" col
      (Packages and pricing, Request a demo); copyright 2026 + tagline
- [x] Skip link, favicon, meta description default

## Notes
- Pricing/Enterprise/Contact pages keep their current copy and structure;
  they inherit the new theme via shared CSS (their content inventory is
  guarded by PagesTest assertions).
