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
