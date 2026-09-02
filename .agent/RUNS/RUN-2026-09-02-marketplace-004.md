# RUN RUN-2026-09-02-marketplace-004

## Task
TASK-003-render-styling-fix (debugging run; resumes deployment work begun
in TASK-002-docker-deployment / RUN-2026-09-02-marketplace-003).

## Agent Role
Full-Stack Engineer (Laravel / Blade) — debugging run.

## Result
COMPLETED (code fix applied and pushed; live verification pending user
redeploy on Render).

## Resume Notes
- Repository state at resume: clean `main` at commit f4d74ca (docs-only
  trailing commit after RUN-003's merge of feature/TASK-002-docker-deployment).
- `.agent/` was fully populated by prior runs: PROJECT.md, ADR-001..004,
  OBS-001..005, RUN-002 and RUN-003 records + ledgers, snapshots, and
  TASK-001 / TASK-002 task files.
- TASK-002 (Docker deployment) is COMPLETED per its own run record — image
  builds cleanly per ADR-004. The Render-deployed image is therefore not
  broken at the Docker/Apache layer; the styling bug lives in the
  application layer (URL generator scheme resolution).
- OBS-005 (web SAPI env visibility) was directly relevant: it confirmed
  that env-var-driven configuration is fragile in this stack, so a code-
  level fix (forceScheme) is preferable to a Render-dashboard env var.

## Summary
User reported the deployed Render app showed raw, unstyled HTML. Diagnosed
the cause as Laravel's `asset()` helper emitting `http://` URLs behind
Render's TLS-terminating proxy, triggering mixed-content blocking on the
HTTPS-served page. Applied a surgical, proxy-aware `URL::forceScheme('https')`
in `AppServiceProvider::boot()` that triggers on `request->isSecure()` OR
a raw `X-Forwarded-Proto: https` header check. Local dev (`php artisan serve`)
is unaffected because neither condition is true on http://localhost:8000.

## Changes Made
- Added proxy-aware HTTPS scheme forcing in `app/Providers/AppServiceProvider::boot()`.
- Wrote OBS-006 documenting the mixed-content failure mode and evidence.
- Wrote ADR-005 recording the decision, alternatives considered, and consequences.
- Wrote TASK-003 task file (this run's task record).
- Wrote this run record and its activity ledger.
- Wrote SNAPSHOT-RUN-2026-09-02-marketplace-004 state snapshot.

## Files Changed
- app/Providers/AppServiceProvider.php
- .agent/OBSERVATIONS/OBS-006-mixed-content-blocking-on-tls-paas.md (new)
- .agent/DECISIONS/ADR-005-force-https-scheme-behind-proxy.md (new)
- .agent/TASKS/TASK-003-render-styling-fix.md (new)
- .agent/RUNS/RUN-2026-09-02-marketplace-004.md (new, this file)
- .agent/RUNS/RUN-2026-09-02-marketplace-004.ledger.md (new)
- .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-004.md (new)

## Commits Created
- 38a2412 — fix(assets): force https:// scheme behind TLS-terminating proxies (Render)

## Branches
- main (direct commit — single-line-code fix + .agent/ docs, no
  feature branch warranted per Section 7.1's "task-specific branch"
  guidance; this is a one-commit hotfix on a previously-merged task)

## Merge Status
- Not applicable — committed directly to main. No feature branch.

## Verification
- Static review of Laravel 11+ URL generator behavior: PASS
  (asset() uses request scheme when ASSET_URL is unset; forceScheme
  overrides formatScheme's default).
- Confirmed CSS files exist and are tracked: PASS
  (public/css/app.css, public/css/fonts.css, public/fonts/*.woff2 present).
- Confirmed Dockerfile / vhost / entrypoint / .htaccess are correct: PASS
  (per ADR-004 and direct inspection — no changes needed).
- Render live verification: PENDING (no PHP/Docker runtime in this
  environment; user must rebuild + redeploy on Render to confirm).

## Discoveries
- OBS-006: mixed-content blocking is the failure mode for any
  TLS-terminating PaaS deployment of this app without forceScheme.
- The shipped `.env`'s `APP_URL=http://localhost:8000` is NOT the direct
  cause (asset() doesn't consult it in HTTP request context), but it IS
  wrong for production and should be overridden via a Render env var for
  non-HTTP-context URL generation (queue/mail/signed routes). This app
  has none of those, so it's currently a cosmetic concern only.

## Decisions
- ADR-005: proxy-aware `URL::forceScheme('https')` in AppServiceProvider,
  with belt-and-braces `X-Forwarded-Proto` header fallback. Rejected
  alternatives: unconditional force in production (breaks local dev),
  ASSET_URL on Render dashboard (requires user setup + entrypoint
  materialization per OBS-005), explicit TrustProxies config (redundant),
  relative asset paths (loses asset() flexibility).

## Problems / Blockers
- Cannot run Laravel locally (no PHP runtime in this environment) — fix
  verified by static review of URL generator source code and confirmed
  by code-level reasoning, not by running the app.
- Cannot run Docker locally (no daemon — OBS-004) — Render-side
  verification must be done by the user via a fresh rebuild + redeploy.

## Remaining Work
- User: trigger a fresh Render rebuild (push to main already done by this
  run; Render auto-builds on push). Visit the deployed URL and confirm
  the styled UI renders. If still unstyled, capture the rendered HTML
  `<link>` tags and the browser network panel for the CSS request — the
  next most likely cause would be Apache's `AllowOverride All` not being
  honored or a stale image cache on Render's edge.

## Next Agent Notes
- The fix is in `app/Providers/AppServiceProvider::boot()`. Do NOT remove
  it without confirming the deployment target no longer sits behind a
  TLS-terminating proxy (ADR-005, OBS-006).
- If the user reports the fix did NOT work, the next debugging step is
  to capture the actual rendered HTML — the `<link href="...">` URLs in
  the page source will tell you whether asset() is emitting the right
  scheme (the fix worked but the browser cached the broken version) or
  whether something else is wrong (e.g., CSS 404s, MIME type mismatch).
- Do NOT modify the Dockerfile, vhost, or entrypoint for this bug — they
  are confirmed correct.
- Do NOT add `ASSET_URL` materialization to the entrypoint — unnecessary
  for HTTP request asset generation; would add complexity for no benefit.
