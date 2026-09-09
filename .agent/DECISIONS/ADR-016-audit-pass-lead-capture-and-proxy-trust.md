# ADR-016 — Audit pass: lead capture actually lands, proxy trust, header hygiene (2026-09-09)

## Context

A cross-repo audit found the storefront's ONLY persistence path — the
contact-form log line (ADR-013) — was silently broken in the shipped
configuration, plus three smaller deployment/security gaps.

## Decisions

1. **`LOG_LEVEL=info` ships in `.env.example`.** The previous
   `LOG_LEVEL=warning` filtered `Log::info` records out at the Monolog
   handler on BOTH deploy targets (both entrypoints materialize `.env` from
   `.env.example`): the form returned the thank-you page while every lead
   evaporated. A feature test now pins the shipped level (must be ≤ info).

2. **`trustProxies(at: '*')` in `bootstrap/app.php`.** Both Render and
   Vercel front every request with a reverse proxy; untrusted proxies made
   the contact-form throttle key on the EDGE IP — a global 5-per-minute
   budget for the whole site. Trusting the proxy keys the limiter on the
   real visitor IP. (The old comment claiming Laravel 11+ trusts `*` by
   default only holds on Laravel Cloud hosts.)

3. **Security headers on every page** (`SetSecurityHeaders` web middleware):
   `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`,
   `Referrer-Policy: strict-origin-when-cross-origin`, and HSTS once the
   request is HTTPS.

4. **`/lang/{locale}` no longer follows cross-site referers.**
   `redirect()->back()` prefers the raw Referer header, which made the
   locale endpoint an open-redirect handoff from a trustworthy domain. Same
   -app referers are honored; anything else lands on the landing page.

5. **A failed log write degrades gracefully.** The log IS the persistence:
   a Monolog write failure used to surface as a generic 500 and lose the
   thank-you UX. The failure is now reported and the flow still completes.

6. **Image/context hygiene:** `.env`, `.env.*` and the stray empty
   `package-lock.json` are `.dockerignore`d (a local `.env` with a real
   `APP_KEY` was previously baked into BOTH images and even preferred by
   the FrankenPHP entrypoint over `.env.example`); the dead
   `docker/entrypoint.vercel.sh` + `docker/apache/vhost.vercel.conf` (they
   still referenced a SQLite migration and the removed `/__debug` endpoint)
   are deleted; `composer run setup` no longer runs npm installs or
   migrations (stateless = no Node, no DB — ADR-013); the entrypoint's
   `APP_KEY set:` diagnostic actually reports the truth now; the session
   cookie defaults to `Secure`; the hero copy chip is keyboard-operable
   (Enter/Space) per WCAG 2.1.1; the contact page carries its own meta
   description in both languages.

## Consequences

- No route or visual changes. 20 tests pass (3 new regression guards),
  Pint clean. `README.md` gains the two undocumented routes and the
  correct "Datum v3" design section; `README.es.md` now exists — the repo
  ships bilingual docs everywhere and the README was the last holdout.
