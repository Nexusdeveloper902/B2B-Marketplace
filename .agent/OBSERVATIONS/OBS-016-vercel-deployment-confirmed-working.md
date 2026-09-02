# OBS-016

## Date
2026-09-02

## Observation
The Vercel deployment of the Presence Platform Marketplace Storefront is
**confirmed working** as of commit `e896c84` (with cleanup in commit
following this observation). After 11 debugging runs (RUN-002 through
RUN-011) spanning Apache+mod_php → FrankenPHP, deprecated `@vercel/docker`
→ `services` config, and a cascade of storage/env var fixes, the
deployment serves the styled landing page correctly.

## Evidence
- User confirmation on 2026-09-02: "Yes the fix works properly."
- The fix that resolved the final issue (commit `e896c84`): loading ALL
  env vars from `.env` via `set -a; . ./.env; set +a` in the entrypoint,
  which ensures `APP_LOCALE`, `APP_KEY`, and all other config values
  are available to Laravel via FrankenPHP's native env var passing.
- Prior to this fix, the cascade of errors was:
  1. Empty 500 (bootstrap crash, OBS-013) → fixed by storage env overrides
  2. `ArgumentCountError: Manager::createDriver()` (OBS-014) → fixed by APP_MAINTENANCE_DRIVER=cache
  3. `MissingAppKeyException` (OBS-015) → fixed by APP_KEY export
  4. `foreach() argument must be of type array|object, string given` (OBS-015) → fixed by loading ALL .env vars

## Impact
- The Vercel deployment is now a viable demo target alongside Render.
- Contact submissions on Vercel remain ephemeral (OBS-007) — lost on cold
  restart. This is acceptable for a marketing demo.
- The Render deployment continues to work unchanged (persistent SQLite
  volume).
- Future agents can use Vercel as a deployment target without further
  debugging — the configuration is stable.

## Related Task
TASK-010-vercel-storage-env-overrides (COMPLETED)

## Status
CONFIRMED — by user verification. The Vercel deployment serves the
styled landing page at `b2-b-marketplace-opal.vercel.app`.
