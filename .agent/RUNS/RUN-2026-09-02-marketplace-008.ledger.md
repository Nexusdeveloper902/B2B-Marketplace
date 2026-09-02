# Activity Ledger — RUN-2026-09-02-marketplace-008

## Resume investigation
- ACTION: re-establish repo state
- COMMAND: git config core.fileMode false && git pull --ff-only origin main
- RESULT: clean main at ccc2116.

## Receive user-reported Vercel runtime crash (persistent)
- USER REPORT on 2026-09-02: same `DatabaseManager.php line 226` error
  on every request, new deployment ID `dpl_5szrpPS4WyTZTjqdYs5xEy7to2zh`.
  RUN-007's `.env` materialization fix (ADR-008) had no effect.

## Diagnose
- INSPECTION:
  - config/session.php line 21: `'driver' => env('SESSION_DRIVER', 'database')`
    — default is `database`, not `file`.
  - config/cache.php line 18: `'default' => env('CACHE_STORE', 'database')`
    — default is `database`, not `file`.
  - config/database.php line 38: `'database' => env('DB_DATABASE', database_path('database.sqlite'))`
    — default is the read-only image path.
- DIAGNOSIS (OBS-010): Vercel's filesystem may be read-only for
  `sed -i .env`. Without .env override, Laravel falls back to config
  defaults — `database` for session/cache. With `SESSION_DRIVER=database`,
  EVERY request triggers a DB connection (via session middleware) BEFORE
  the controller runs. This is why even `/favicon.ico` crashes.

## Apply fix
- config/database.php: code-level SQLite path fallback to /tmp when
  default directory isn't writable.
- config/session.php: default `database` → `file`.
- config/cache.php: default `database` → `file`.
- routes/web.php: `/__debug` route (no DB access, outputs diagnostics).
- docker/entrypoint.vercel.sh: `set -x` trace + echo diagnostics,
  non-fatal storage symlink, non-fatal migrate.

## Verify
- sh -n docker/entrypoint.vercel.sh: PASS
- Basic PHP structure check: PASS
- Static review: PASS

## Document
- OBS-010, ADR-009, TASK-007, RUN-008, ledger, snapshot: all written.

## Commit + Push
- (hash recorded after commit)
