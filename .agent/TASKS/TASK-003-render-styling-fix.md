# TASK-003-render-styling-fix

## Objective
Restore styling on the production Render deployment of the Presence
Platform Marketplace Storefront. The Docker image built per ADR-004 was
deployed on Render and rendered as raw, unstyled HTML — every page
returned with no CSS applied.

## Origin
User-reported debugging request on 2026-09-02: "I just tried the app
(deployed on render using the docker file) and I got blasted with straight
html, no styling." Treated as a RESUME of the deployment work begun in
TASK-002-docker-deployment (RUN-2026-09-02-marketplace-003), focused on
the production-styling bug, not the original MVP build (TASK-001).

## Diagnosis
Root cause: Laravel's `asset()` helper uses the request scheme (not
`APP_URL`) to build absolute URLs. Render terminates TLS at its load
balancer; the container receives plain HTTP with
`X-Forwarded-Proto: https`. Without an explicit `URL::forceScheme('https')`
or trusted-proxy honoring of the forwarded proto, Blade's
`<link rel="stylesheet" href="{{ asset('css/app.css') }}">` emits
`http://app.onrender.com/css/app.css` on a page served over HTTPS, and
the browser blocks the stylesheet as active mixed content. Self-hosted
fonts in `public/fonts/*` fail for the same reason. Full evidence in
`.agent/OBSERVATIONS/OBS-006-mixed-content-blocking-on-tls-paas.md`.

Not the cause (verified): Apache DocumentRoot (`/var/www/html/public`),
mod_rewrite (`a2enmod rewrite` is in the Dockerfile), `.htaccess`
(`RewriteCond %{REQUEST_FILENAME} !-f` lets existing static files pass
through), the CSS files themselves (tracked in git at `public/css/`), or
the committed `APP_URL=http://localhost:8000` (which `asset()` does not
consult in HTTP request context).

## Requirements
- Make the Render deployment render with its CSS / fonts applied.
- Do not break local dev (`php artisan serve` on http://localhost:8000).
- Do not require the user to set env vars on Render's dashboard.
- Do not introduce Node/Vite/asset-pipeline changes.
- Do not modify the Dockerfile, the vhost, or the entrypoint — they are
  correct (ADR-004).

## Acceptance Criteria
- [x] After a fresh Render rebuild + redeploy, all 5 pages render with
      the "Event Ledger" visual design (per ADR-002) — styled, not raw
      HTML. (Pending user-side verification; cannot run Render from this
      environment.)
- [x] Local dev (`php artisan serve`) on `http://localhost:8000` still
      renders correctly over HTTP without redirect loops or mixed-content
      errors. (Static review only — no PHP runtime in this environment.)
- [x] Fix is documented in `.agent/DECISIONS/ADR-005-*` and a corroborating
      observation in `.agent/OBSERVATIONS/OBS-006-*`.
- [x] No new dependencies, no Dockerfile changes, no entrypoint changes.
- [x] Fix is committed to `main` and pushed.

## Implementation
Surgical change to `app/Providers/AppServiceProvider::boot()` — add a
proxy-aware HTTPS force that calls `URL::forceScheme('https')` when the
current request arrived over HTTPS (directly or behind a TLS-terminating
proxy). The raw `X-Forwarded-Proto` header check makes the fix independent
of TrustProxies middleware state. Local dev on http://localhost:8000 is
unaffected because neither condition is true there.

See ADR-005 for the full reasoning, alternatives considered, and the
consequences future agents must preserve.

## Commits

### Commit — {{COMMIT_HASH}}
Date: 2026-09-02
Branch: main

Summary:
fix(assets): force https:// scheme behind TLS-terminating proxies (Render)

The deployed Docker image on Render rendered as unstyled HTML because
asset() emitted http:// URLs on an HTTPS page (mixed-content blocked).

Changes:
- app/Providers/AppServiceProvider.php: add proxy-aware URL::forceScheme
  in boot() — triggers on request->isSecure() OR raw X-Forwarded-Proto
  header. Local php artisan serve is unaffected.
- .agent/OBSERVATIONS/OBS-006-mixed-content-blocking-on-tls-paas.md:
  record the failure mode and evidence.
- .agent/DECISIONS/ADR-005-force-https-scheme-behind-proxy.md: record the
  decision, alternatives, and reasoning.
- .agent/TASKS/TASK-003-render-styling-fix.md: this file.
- .agent/RUNS/RUN-2026-09-02-marketplace-004.md: run record.
- .agent/RUNS/RUN-2026-09-02-marketplace-004.ledger.md: activity ledger.
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-004.md: post-run state.

Verification:
- Static review of asset() / URL generator behavior in Laravel 11+
  (no PHP runtime in this environment).
- Confirmed CSS files exist in public/css/ and are tracked in git.
- Confirmed Apache DocumentRoot, mod_rewrite, and .htaccess are correct
  per ADR-004 and Dockerfile inspection.
- Render-side verification pending user redeploy.

Notes:
- Did NOT modify Dockerfile, vhost, entrypoint, .env, or bootstrap/app.php
  — none were the cause, and minimal-change principle (Section 1.4) applies.
- Did NOT add ASSET_URL materialization to the entrypoint — unnecessary
  for this app (asset() uses request context, not APP_URL, in HTTP
  requests). Documented in ADR-005 as a rejected alternative.
- Did NOT add explicit trustProxies(at: '*') to bootstrap/app.php —
  Laravel 11+ defaults to this, and the raw header check makes the fix
  independent of TrustProxies state. Documented in ADR-005.

## Remaining Work
- User redeploy on Render and confirm the styled UI renders. If still
  unstyled, next debug step: capture the actual rendered HTML and the
  network panel — most likely an additional bug would be Apache not
  honoring .htaccess (verify AllowOverride All) or Render serving from
  a non-DocumentRoot path.
