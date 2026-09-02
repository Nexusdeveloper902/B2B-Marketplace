<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the storefront locale for every web request.
 *
 * Order of resolution (see ADR-003):
 *   1. an explicit choice stored in the session by the EN/ES toggle;
 *   2. the request's Accept-Language preference, mapped onto the supported
 *      locales (applies only before an explicit choice has been stored);
 *   3. the configured application default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', ['en']);
        $default = config('app.locale', 'en');

        $locale = $request->session()->get('locale');

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = $request->getPreferredLanguage($supported) ?: $default;
            $locale = in_array($locale, $supported, true) ? $locale : $default;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
