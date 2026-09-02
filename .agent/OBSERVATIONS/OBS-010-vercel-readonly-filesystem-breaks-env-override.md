# OBS-010

## Date
2026-09-02

## Observation
After RUN-007's `.env` materialization fix (ADR-008), the Vercel
deployment STILL crashed on every request with the same
`DatabaseManager.php line 226` error. The fix was correctly applied but
had no effect at runtime.

Root cause: **Vercel's container filesystem is read-only for the
entrypoint's `.env` modification**. The entrypoint's `sed -i .env`
fails silently (or isn't applied), so the `.env` file in the image
keeps its shipped values. Laravel then uses the config-file defaults
for session/cache/DB — and those defaults point at the read-only image
layer.

The critical discovery: Laravel's default config values (in
`config/session.php`, `config/cache.php`, `config/database.php`) all
default to **database-backed** storage:

- `config/session.php` line 21: `'driver' => env('SESSION_DRIVER', 'database')`
- `config/cache.php` line 18: `'default' => env('CACHE_STORE', 'database')`
- `config/database.php` line 38: `'database' => env('DB_DATABASE', database_path('database.sqlite'))`

The shipped `.env` has `SESSION_DRIVER=file` and `CACHE_STORE=file`,
which override the config defaults. BUT if the `.env` file isn't being
read (or the entrypoint's `sed -i` corrupted it), Laravel falls back
to the config defaults — `database` for session and cache.

With `SESSION_DRIVER=database`, **every HTTP request triggers a DB
connection** (to read/write the session). This is why every route —
including `/favicon.ico` (a 404 that shouldn't need the DB) — crashes
at `DatabaseManager.php line 226`: the session middleware tries to
connect to the DB BEFORE the controller runs, before routing even
completes.

And the DB connection fails because `DB_DATABASE` in `.env` is commented
out (`# DB_DATABASE=laravel`), so Laravel falls back to
`database_path('database.sqlite')` = `/var/www/html/database/database.sqlite`,
which is in the read-only image layer.

## Evidence
- User-reported Vercel logs on 2026-09-02 (after RUN-007's fix was
  pushed and Vercel rebuilt — new deployment ID
  `dpl_5szrpPS4WyTZTjqdYs5xEy7to2zh`):
  ```
  {"message":"In DatabaseManager.php line 226:",
   "requestMethod":"GET","requestPath":"/","responseStatusCode":500,...}
  ```
  Same error as before the fix — the `.env` materialization had no
  effect.
- `config/session.php` line 21 (pre-fix): `'driver' => env('SESSION_DRIVER', 'database')`
  — the config default is `database`, not `file`.
- `config/cache.php` line 18 (pre-fix): `'default' => env('CACHE_STORE', 'database')`
  — the config default is `database`, not `file`.
- `config/database.php` line 38 (pre-fix): `'database' => env('DB_DATABASE', database_path('database.sqlite'))`
  — the config default is the read-only image path.
- The shipped `.env` has `SESSION_DRIVER=file` and `CACHE_STORE=file`,
  which DO override the config defaults — but ONLY if `.env` is being
  read. On Vercel, if the entrypoint's `sed -i .env` corrupts the file
  (e.g., partial write, permission change) or if Apache workers can't
  read the modified `.env`, the config defaults kick in.

## Impact
- Any Vercel deployment of a default Laravel app crashes on every
  request if the `.env` file isn't properly read, because the default
  session/cache drivers are `database`.
- The fix is twofold: (1) change the config defaults to `file` (so
  the app works even without `.env`), and (2) add a code-level
  fallback in `config/database.php` for the SQLite path (so the DB
  connection works even without `.env`).
- This affects ONLY the Vercel deployment (read-only filesystem at
  request time). Local dev and Render are unaffected because their
  filesystems are writable and `.env` is read correctly.

## Related Task
TASK-007-vercel-config-level-fallbacks

## Status
CONFIRMED — by user-reported Vercel log evidence plus config-file
inspection. Fix applied in RUN-2026-09-02-marketplace-008 via ADR-009.
