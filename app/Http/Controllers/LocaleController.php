<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Stores an explicit locale choice from the header EN/ES toggle and sends
     * the visitor back where they came from.
     *
     * The fallback here must NOT be redirect()->back()'s referer behavior:
     * back() prefers the raw Referer header, and a cross-site link to
     * /lang/es with an attacker Referer would turn the storefront into an
     * open-redirect handoff. Only same-app referers are honored; anything
     * else lands on the in-app page the visitor is most plausibly on (the
     * landing page, which carries the toggle in its header).
     */
    public function switch(string $locale): RedirectResponse
    {
        if (in_array($locale, config('app.supported_locales', ['en']), true)) {
            session()->put('locale', $locale);
        }

        $referer = request()->header('referer');
        $sameApp = $referer !== null
            && str_starts_with($referer, rtrim(url('/'), '/'))
            ? $referer
            : null;

        return $sameApp !== null
            ? redirect()->to($sameApp)
            : redirect()->route('landing');
    }
}
