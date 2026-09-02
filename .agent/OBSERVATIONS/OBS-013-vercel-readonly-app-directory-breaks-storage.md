# OBS-013

## Date
2026-09-02

## Observation
Vercel's container filesystem is **read-only at request time** for the
`/app` directory (where the application code lives). This means:

1. `storage/framework/views/` — where Laravel compiles Blade templates —
   is NOT writable at request time.
2. `storage/framework/sessions/` — where file-based sessions are stored —
   is NOT writable.
3. `storage/framework/cache/data/` — where file-based cache is stored —
   is NOT writable.
4. `storage/logs/` — where Laravel writes log files — is NOT writable.

The entrypoint's `rm -rf storage && ln -s /tmp/storefront/storage storage`
approach (from RUN-010 / ADR-011) fails silently because `/app` itself
is read-only — you can't `rm` or `ln -s` in a read-only directory.

Without env var overrides, Laravel crashes during bootstrap with a
generic 500 (empty response body, `size:0`, 15-70ms duration) because
it tries to write compiled views, sessions, or cache files to the
read-only `storage/` directory.

## Evidence
- User-reported Vercel logs (2026-09-02, deployment `dpl_54s6x8TYvNPLU6M7ven93cTkirkj`):
  ```
  "status":500,"size":0,"duration":0.069582282
  "resp_headers":{"Server":["FrankenPHP Caddy"],"X-Powered-By":["PHP/8.4.25"]}
  ```
  Empty response body + fast crash = bootstrap failure before the error
  handler can render.
- The `set -x` entrypoint trace (added in RUN-010) confirmed the
  entrypoint ran, but the `rm -rf storage` step failed silently (no
  error in logs because of `2>/dev/null`).
- Vercel docs confirm: "the filesystem is ephemeral and not persisted
  across deployments" — and in practice, the image layer is read-only
  at request time for container functions.

## Impact
- Any Laravel-on-Vercel deployment that uses file-based drivers
  (SESSION_DRIVER=file, CACHE_STORE=file, default view compilation)
  will crash on every request during bootstrap.
- The fix is to override ALL storage paths via environment variables
  (which FrankenPHP reads natively): VIEW_COMPILED_PATH → /tmp,
  SESSION_DRIVER=cookie, CACHE_STORE=array, LOG_CHANNEL=stderr.
- The Render deployment is unaffected — Render's container filesystem
  IS writable at request time.

## Related Task
TASK-010-vercel-storage-env-overrides

## Status
CONFIRMED — by user-reported Vercel logs showing empty 500 responses
with `size:0`. Fix applied in commit `51e7b70` (RUN-011).
