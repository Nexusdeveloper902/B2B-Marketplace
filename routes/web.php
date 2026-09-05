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
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')
    ->middleware('throttle:5,1');
Route::get('/contact/thank-you', [PageController::class, 'thankYou'])->name('contact.thankYou');

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| No debug endpoint
|--------------------------------------------------------------------------
| The /__debug diagnostic endpoint used during the Vercel bring-up
| (RUN-005..RUN-011) was removed in TASK-011: it was unauthenticated and
| disclosed environment details (DB paths, writability, env values).
| Deployment diagnosis now uses the platform build/runtime logs.
| See .agent/DECISIONS/ADR-013-stateless-no-database.md.
*/
