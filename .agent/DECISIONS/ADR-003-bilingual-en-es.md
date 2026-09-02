# ADR-003: Bilingual English/Spanish implementation (session-based locale)

## Date
2026-09-02

## Context
Extra requirement attached to RUN-2026-09-02-marketplace-002: "The app must have
both an English and Spanish mode." The storefront is server-rendered Blade with no
SPA state, so the mechanism must be simple, deterministic, and testable.

## Decision
Session-based locale switching using Laravel's built-in localization:

- All user-facing copy lives in `lang/en/*.php` and `lang/es/*.php` translation
  files, consumed in Blade via `__('section.key')`.
- `app/Http/Middleware/SetLocale.php` runs in the `web` group after
  `StartSession`. Resolution order:
  1. `session('locale')` if it is one of `en`/`es`;
  2. else the request's `Accept-Language` preference mapped onto `en`/`es`
     (first visit only, before any explicit choice is stored);
  3. else the app default `en`.
- A `GET /lang/{locale}` route stores the choice in the session and redirects
  back (fallback `/`). The header renders a persistent EN/ES toggle showing the
  active language.
- `<html lang="...">` reflects the active locale per page render.
- Contact-form validation messages are translatable custom messages pulled from
  `lang/{locale}/forms.php`, so server-side errors display in the active language.
- Default locale: `en` (`APP_LOCALE=en`, `APP_FALLBACK_LOCALE=en`).

## Alternatives Considered
- Locale-prefixed URLs (`/es/pricing`) — better for SEO, but SEO is explicitly out
  of scope; prefixed routes would double the route surface and every internal link
  for no judged benefit.
- Browser-language only (no toggle) — fails the "both modes" requirement for a
  shared-demo machine where the browser locale is fixed.
- Query-string locale (`?lang=es`) — leaks the parameter into every link and
  breaks redirect-back simplicity.

## Reasoning
Laravel's native localization is designed exactly for this; session persistence
gives users a sticky choice with a visible toggle; no new dependencies; trivially
testable (`session('locale')` then re-request).

## Consequences
- URLs are locale-independent: content language is per-visitor.
- If SEO ever matters, a future ADR should introduce prefixed URLs; that would
  require updating every internal link and the locale tests.
- Every new piece of UI copy MUST be added to both `lang/en/` and `lang/es/` at
  the same time. Missing keys fall back to English (Laravel fallback behavior).

## Status
ACTIVE

## Supersedes
None
