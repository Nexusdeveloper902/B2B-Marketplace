<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Stores an explicit locale choice from the header EN/ES toggle and sends
     * the visitor back where they came from.
     */
    public function switch(string $locale): RedirectResponse
    {
        if (in_array($locale, config('app.supported_locales', ['en']), true)) {
            session()->put('locale', $locale);
        }

        return redirect()->back(fallback: route('landing'));
    }
}
