# OBS-014

## Date
2026-09-02

## Observation
Laravel's `MaintenanceModeManager` crashes during bootstrap on Vercel
with:

```
ArgumentCountError: Too few arguments to function
Illuminate\Support\Manager::createDriver(), 0 passed in
Manager.php on line 105 and exactly 1 expected
at FoundationServiceProvider.php:295
```

Root cause: the `PreventRequestsDuringMaintenance` middleware runs on
every request during bootstrap. It resolves the `MaintenanceModeManager`,
which calls `getDefaultDriver()`. The default maintenance driver is
`'file'` (from `config/app.php`: `env('APP_MAINTENANCE_DRIVER', 'file')`).
The `file` driver writes to `storage/framework/` — which is read-only on
Vercel (OBS-013). When the driver can't initialize, `getDefaultDriver()`
returns null, and `createDriver()` is called with 0 arguments → crash.

## Evidence
- User-reported Vercel error page (2026-09-02, deployment
  `dpl_HHEBJG2TGhq4FWr471bbzQt3EUd7`) with full stack trace:
  ```
  ArgumentCountError: Too few arguments to function
  Manager::createDriver(), 0 passed... at
  FoundationServiceProvider.php:295
  ```
  Stack trace frame 10: `PreventRequestsDuringMaintenance.php:65`
- `config/app.php` line 135-137:
  ```php
  'maintenance' => [
      'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
      'store' => env('APP_MAINTENANCE_STORE', 'database'),
  ],
  ```
- Laravel 13.30.1 `FoundationServiceProvider.php` line 252:
  ```php
  fn () => $this->app->make(MaintenanceModeManager::class)->driver()
  ```
  This calls `driver()` with no argument, which calls
  `getDefaultDriver()`, which returns null when the `file` driver
  can't initialize on a read-only filesystem.

## Impact
- Every request crashes during bootstrap because the maintenance mode
  middleware runs globally on all routes.
- The fix: set `APP_MAINTENANCE_DRIVER=cache` and
  `APP_MAINTENANCE_STORE=array` via env vars. The `cache` driver with
  `array` store requires no file I/O and no DB I/O — per-request,
  non-persistent, but we never enable maintenance mode on this demo.
- The Render deployment is unaffected — the `file` driver works on
  Render's writable filesystem.

## Related Task
TASK-010-vercel-storage-env-overrides

## Status
CONFIRMED — by user-reported Vercel error page with full stack trace.
Fix applied in commit `6874fc7` (RUN-011).
