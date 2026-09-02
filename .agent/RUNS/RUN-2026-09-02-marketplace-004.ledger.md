# Activity Ledger — RUN-2026-09-02-marketplace-004

## Resume investigation
- ACTION: clone repository with provided GitHub token
- COMMAND: git clone https://x-access-token:***@github.com/Nexusdeveloper902/B2B-Marketplace.git
- RESULT: SUCCESS — repository at /home/z/my-project/debug/B2B-Marketplace

- ACTION: establish git baseline
- COMMAND: git log --oneline -20 && git branch -a && git status
- RESULT: clean main at f4d74ca; remote branches feature/TASK-001-marketplace-mvp
  and feature/TASK-002-docker-deployment both already merged.

## Recover prior task documentation
- FILES:
  - .agent/PROJECT.md
  - .agent/TASKS/TASK-001-marketplace-mvp.md (prior run, completed)
  - .agent/DECISIONS/ADR-001..004
  - .agent/OBSERVATIONS/OBS-001..005
  - .agent/RUNS/RUN-2026-09-02-marketplace-002.md / -003.md
- RESULT: FOUND — full .agent/ tree populated by prior runs. ADR-004
  documents the Docker approach; OBS-005 documents the web SAPI env-var
  visibility quirk that informed the fix design.

## Diagnose reported bug
- SYMPTOM: "blasted with straight HTML, no styling" on Render-deployed image.
- INSPECTION:
  - resources/views/layouts/app.blade.php — uses {{ asset('css/fonts.css') }}
    and {{ asset('css/app.css') }} in <link> tags.
  - config/app.php — no asset_url key, so ASSET_URL env is unused.
  - app/Providers/AppServiceProvider.php — empty boot() body; no
    URL::forceScheme, no TrustProxies customization.
  - .env — APP_ENV=production, APP_URL=http://localhost:8000.
  - Dockerfile / docker/apache/vhost.conf / docker/entrypoint.sh / .htaccess —
    all correct per ADR-004 (DocumentRoot=/var/www/html/public, mod_rewrite
    enabled, AllowOverride All, RewriteCond %{REQUEST_FILENAME} !-f passes
    existing static files through).
- DIAGNOSIS: Laravel's asset() helper uses request scheme (not APP_URL)
  when ASSET_URL is unset. Render terminates TLS at its load balancer;
  the container receives plain HTTP with X-Forwarded-Proto: https. Without
  forceScheme('https') or trusted-proxy honoring of the forwarded proto,
  asset URLs emit as http://app.onrender.com/... on an HTTPS page →
  browser blocks them as active mixed content → no styling. Recorded in
  OBS-006.

## Apply fix
- FILE: app/Providers/AppServiceProvider.php
- CHANGE: add proxy-aware URL::forceScheme('https') in boot(), triggered
  when request->isSecure() OR raw X-Forwarded-Proto header equals 'https'.
  Local php artisan serve (http://localhost:8000, no proxy header) is
  unaffected. Decision recorded in ADR-005.

## Document
- FILES:
  - .agent/OBSERVATIONS/OBS-006-mixed-content-blocking-on-tls-paas.md
  - .agent/DECISIONS/ADR-005-force-https-scheme-behind-proxy.md
  - .agent/TASKS/TASK-003-render-styling-fix.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-004.md
  - .agent/RUNS/RUN-2026-09-02-marketplace-004.ledger.md (this file)
  - .agent/STATE/SNAPSHOT-RUN-2026-09-02-marketplace-004.md
- RESULT: SUCCESS — all append-only records created per protocol
  Sections 11-17. No historical records modified or overwritten.

## Commit
- ACTION: commit fix + .agent/ docs to main
- BRANCH: main
- MESSAGE: fix(assets): force https:// scheme behind TLS-terminating proxies (Render)
- RESULT: (hash recorded in TASK-003 commit entry after git commit)

## Push
- ACTION: push main to origin
- COMMAND: git push origin main
- RESULT: (recorded after push)

## Verification
- Static review of asset() / URL generator behavior: PASS
- Confirmed CSS files exist and are tracked in git: PASS
- Confirmed Dockerfile / vhost / entrypoint / .htaccess correct: PASS
- Render live verification: PENDING user redeploy
