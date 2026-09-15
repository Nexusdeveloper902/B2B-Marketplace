# TASK-014 — Marketplace catalog layer

## Date opened
2026-09-15

## Origin
Owner: same info and colors, more visually striking, feel like a real
marketplace instead of a product page — without inventing content.

## Delivered
- Views: header (utility bar, quote button), footer (catalog column),
  landing (catalog home), pricing (listings + comparison), product
  (PDP + buy box), enterprise (dark hero + catalog cases), contact
  (stepper + summary, `?tier=` preselect); partials quote-drawer,
  quote-add, breadcrumb, tier-specs, module-foot, icon.
- `lang/{en,es}/store.php` — UI labels only.
- `public/css/app.css` marketplace layer + tokens; `public/js/store.js`.
- Tests: `tests/Feature/StorefrontCatalogTest.php` (8).
- Docs: FRONTEND.md/.es.md "Marketplace layer" + C1 note; ADR-018.

## Verification
- `php artisan test` 28/28 (173 assertions); `pint --test` pass.
- Copy preservation: every pre-existing lang leaf string (EN + ES)
  rendered on the new pages (only unused/validation-only strings absent,
  as before).
- Headless Chrome (CDP) flow: add from pricing/home → badge count →
  drawer lists items → CTA carries `?tier=campus` → contact prefilled +
  preselected → remove updates → PDP picker retargets add → ES labels;
  zero console errors.
- 390px: no horizontal overflow on any page, EN and ES.
- `scripts/contrast_audit.py`: ALL PAIRS PASS.
