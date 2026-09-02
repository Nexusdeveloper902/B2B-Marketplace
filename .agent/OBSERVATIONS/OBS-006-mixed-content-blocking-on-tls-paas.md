# OBS-006

## Date
2026-09-02

## Observation
A Laravel + Apache + mod_php storefront deployed behind a TLS-terminating
PaaS proxy (Render, Fly.io, Heroku, etc.) renders as unstyled HTML because
modern browsers block the generated asset URLs as active mixed content.

Symptom (user-reported): "blasted with straight HTML, no styling."

Mechanism:
1. PaaS terminates TLS at its load balancer and forwards plain HTTP to the
   container with `X-Forwarded-Proto: https` (and usually `X-Forwarded-Host`).
2. Laravel's `asset()` helper uses the request scheme (not `APP_URL` from
   `.env`) to build absolute URLs when `ASSET_URL` is unset.
3. Without `URL::forceScheme('https')` or active TrustProxies honoring
   `X-Forwarded-Proto`, `asset('css/app.css')` emits
   `http://app.onrender.com/css/app.css`.
4. The page is served over HTTPS, but the `<link rel="stylesheet">` points
   to `http://...` — active mixed content. The stylesheet (and self-hosted
   fonts in `public/fonts/*`) never loads.
5. The user sees raw, unstyled HTML.

Why `APP_URL=http://localhost:8000` in the committed `.env` is not the
direct cause: `asset()` does NOT consult `config('app.url')` in HTTP
request context — that value is used by Artisan / queue workers / signed
routes / mail. It IS wrong for production and should be overridden via a
Render env var, but it is not the styling bug.

## Evidence
- Repository inspection: `resources/views/layouts/app.blade.php` calls
  `{{ asset('css/fonts.css') }}` and `{{ asset('css/app.css') }}`.
- `config/app.php` has no `asset_url` key (so `ASSET_URL` env is unused).
- `app/Providers/AppServiceProvider.php` had an empty `boot()` body — no
  `URL::forceScheme(...)`, no TrustProxies customization.
- The previously-shipped `.env` has `APP_ENV=production` and
  `APP_URL=http://localhost:8000` — the production env without HTTPS
  forcing is what triggers the bug behind Render's proxy.
- OBS-005 already documents that container env vars do not reliably reach
  the mod_php web SAPI; the same caveat applies if `APP_URL`/`ASSET_URL`
  were to be set on Render's dashboard — the entrypoint would have to
  materialize them into `.env` for the web worker to see them.

## Impact
Any production deployment of this app behind a TLS-terminating proxy shows
unstyled HTML unless the AppServiceProvider is updated (or `ASSET_URL` is
materialized into `.env`). The Docker image itself is correct; the bug is
in the URL generator scheme resolution.

## Related Task
TASK-003-render-styling-fix

## Status
CONFIRMED — fix applied in RUN-2026-09-02-marketplace-004 (commit pending
in this run); verification requires a fresh Render deploy.
