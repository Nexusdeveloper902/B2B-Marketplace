<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Presence Platform — Marketplace Storefront
|--------------------------------------------------------------------------
| Five public pages plus the locale toggle and the contact form submission.
| No auth, no cart, no checkout, no vendor accounts (see .agent/PROJECT.md).
*/

Route::get('/', [PageController::class, 'landing'])->name('landing');
Route::get('/product', [PageController::class, 'product'])->name('product');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/enterprise', [PageController::class, 'enterprise'])->name('enterprise');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/contact/thank-you', [PageController::class, 'thankYou'])->name('contact.thankYou');

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Debug endpoint — NO DB ACCESS, safe to call when the DB is broken.
|--------------------------------------------------------------------------
| Outputs DB config, file status, storage writability, and PHP extensions
| as JSON. Used to diagnose Vercel deployment issues (ADR-009 / OBS-010).
| Disabled in local dev — only responds when APP_ENV != local AND the
| request comes with ?token=debug (or when APP_DEBUG is true).
*/
Route::get('/__debug', function () {
    $dbPath = config('database.connections.sqlite.database');
    $storagePath = storage_path();
    return response()->json([
        'db' => [
            'connection' => config('database.default'),
            'configured_path' => $dbPath,
            'env_db_database' => env('DB_DATABASE'),
            'file_exists' => file_exists($dbPath),
            'file_writable' => is_writable($dbPath),
            'dir_exists' => is_dir(dirname($dbPath)),
            'dir_writable' => is_writable(dirname($dbPath)),
        ],
        'storage' => [
            'path' => $storagePath,
            'writable' => is_writable($storagePath),
            'views_writable' => is_writable(storage_path('framework/views')),
            'sessions_writable' => is_writable(storage_path('framework/sessions')),
            'cache_writable' => is_writable(storage_path('framework/cache/data')),
            'logs_writable' => is_writable(storage_path('logs')),
            'is_symlink' => is_link($storagePath),
            'real_path' => is_link($storagePath) ? readlink($storagePath) : null,
        ],
        'session_driver' => config('session.driver'),
        'cache_store' => config('cache.default'),
        'php' => [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'pdo_sqlite' => extension_loaded('pdo_sqlite'),
            'sqlite3' => extension_loaded('sqlite3'),
        ],
        'env' => [
            'APP_ENV' => app()->environment(),
            'APP_DEBUG' => config('app.debug'),
            'APP_KEY_set' => !empty(config('app.key')),
            'APP_URL' => config('app.url'),
        ],
        'filesystem' => [
            'var_www_html_writable' => is_writable(base_path()),
            'env_writable' => is_writable(base_path('.env')),
            'tmp_writable' => is_writable('/tmp'),
        ],
    ], 200, ['Content-Type' => 'application/json']);
})->name('debug');
