# ADR-005

## Date
2026-09-02

## Context
The Docker-deployed storefront (ADR-004) was deployed on Render and rendered
as unstyled HTML. Root cause: Render terminates TLS at its load balancer and
forwards plain HTTP to the container with `X-Forwarded-Proto: https`. The
Blade layout calls `asset('css/app.css')`, which uses the request scheme
(not `APP_URL`) to build the absolute URL. Without an explicit HTTPS force,
the emitted asset URLs come out as `http://app.onrender.com/...` and the
browser blocks them as active mixed content on the HTTPS page (see OBS-006).

The fix needs to:
- Make `asset()`, `url()`, `route()`, and signed URLs emit HTTPS on Render
  (HTTPS-via-proxy).
- Not break local dev (`php artisan serve` on `http://localhost:8000`),
  which runs with the shipped `APP_ENV=production` because the project
  deliberately ships a production-ready `.env`.
- Not require the user to set env vars on the Render dashboard — zero-config
  deploy should "just work," matching ADR-004's deployment posture.
- Not introduce Node/Vite/asset-pipeline changes (out of scope, ADR-002
  design depends on plain CSS in `public/css/`).

## Decision
Add a proxy-aware HTTPS force in `AppServiceProvider::boot()`:

```php
$request = request();
$behindHttpsProxy = $request
    && ($request->isSecure()
        || $request->server('HTTP_X_FORWARDED_PROTO') === 'https');

if ($behindHttpsProxy) {
    URL::forceScheme('https');
}
```

This forces the URL generator to use the `https://` scheme whenever the
current request arrived over HTTPS — either directly (`request->isSecure()`)
or behind a TLS-terminating proxy (raw `X-Forwarded-Proto` header check).
Local dev on `http://localhost:8000` is unaffected because neither
condition is true.

The raw `X-Forwarded-Proto` check is intentionally belt-and-braces: it
works whether or not `TrustProxies` middleware is active. Laravel 11+'s
default `TrustProxies` trusts `*` and would also make `request->isSecure()`
return true behind a proxy, but the explicit header check protects against
any future Laravel patch that changes the default.

## Alternatives Considered
- `URL::forceScheme('https')` unconditionally in production env: rejected —
  it breaks local `php artisan serve`, which the project's `.env`
  intentionally runs in `APP_ENV=production`.
- Setting `ASSET_URL` on Render's dashboard: rejected — requires manual
  user setup and (per OBS-005) the entrypoint would have to materialize
  the env var into `.env` for the mod_php SAPI to see it. Two moving parts.
- Configuring `Middleware::trustProxies(at: '*')` in `bootstrap/app.php`:
  redundant — Laravel 11+ already defaults to this, and the raw
  `X-Forwarded-Proto` check makes the fix independent of TrustProxies
  state anyway.
- Switching the Blade layout to relative asset paths
  (`href="/css/app.css"`): would also fix the bug, but loses Laravel's
  `asset()` host/scheme flexibility for any future multi-domain setup.
  Rejected in favor of fixing the URL generator at the source.

## Reasoning
The bug is in URL generator scheme resolution, not in asset packaging,
Dockerfile, or Apache config. Fixing it at `AppServiceProvider::boot()`
is the smallest change that fixes the bug for every URL the framework
emits, preserves local dev, requires no user-side env var setup, and
remains correct if the app is later moved to a non-proxy HTTPS host.

## Consequences
- Render deployments render styled HTML after a fresh rebuild + redeploy.
- Local `php artisan serve` continues to work over HTTP without change.
- A future agent moving the app to a non-PaaS HTTPS host (direct HTTPS to
  the container) still benefits — `request->isSecure()` returns true and
  the force applies.
- If a future agent switches the app to Vite-compiled assets and removes
  the `public/css/*.css` files, this fix is still required — `asset()`
  still builds the URL for the compiled manifest entries.
- Future agents must NOT remove the `forceScheme('https')` block without
  confirming the deployment target no longer sits behind a TLS-terminating
  proxy. Removing it reintroduces OBS-006.

## Status
ACTIVE

## Supersedes
none
