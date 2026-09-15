# RUN-2026-09-15-marketplace-018 — TASK-014 catalog layer commit

TASK-014 shipped the marketplace catalog layer (ADR-018) and this run
commits it. Scope is exactly the TASK-014 file list: header (utility
bar, quote button), footer (catalog column), landing (catalog home),
pricing (listings + comparison), product (PDP + buy box), enterprise
(dark hero + catalog cases), contact (stepper + summary, `?tier=`
preselect); partials quote-drawer, quote-add, breadcrumb, tier-specs,
module-foot, icon; `lang/{en,es}/store.php` (UI labels only);
`public/css/app.css` marketplace layer; `public/js/store.js`
(new — `public/js/app.js` untouched, runs under reduced motion);
`tests/Feature/StorefrontCatalogTest.php` (8); FRONTEND.md/.es.md
"Marketplace layer" + C1 note.

## Verification (from TASK-014, not re-run in this session)
- `php artisan test` 28/28 (173 assertions); `pint --test` pass.
- Copy preservation: every pre-existing lang leaf string (EN + ES)
  rendered on the new pages (only unused/validation-only strings
  absent, as before).
- Headless Chrome (CDP) flow: add from pricing/home → badge count →
  drawer lists items → CTA carries `?tier=campus` → contact prefilled +
  preselected → remove updates → PDP picker retargets add → ES labels;
  zero console errors. 390px: no horizontal overflow on any page, EN+ES.
- `scripts/contrast_audit.py`: ALL PAIRS PASS.

## Status
DONE 2026-09-15 — committed + pushed to `origin/main` in this run.
The TASK-014 tree had no RUN file (TASKS/ held only TASK-001); this
file closes that gap so the catalog layer follows the TASK + RUN + ADR
convention.
