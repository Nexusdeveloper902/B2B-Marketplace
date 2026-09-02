<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force https:// scheme on every URL the framework emits (asset(),
        // url(), route(), signed routes) when the request arrived over HTTPS —
        // directly or behind a TLS-terminating proxy.
        //
        // Why: on hosts like Render, Fly.io, Heroku, or any load-balanced PaaS,
        // TLS terminates upstream and the container receives a plain HTTP
        // request carrying X-Forwarded-Proto: https. Without this fix, Laravel
        // generates asset URLs with the proxied http:// scheme, e.g.
        //     <link rel="stylesheet" href="http://app.onrender.com/css/app.css">
        // on a page served over HTTPS. Modern browsers block that as active
        // mixed content, so the stylesheet (and the self-hosted fonts) never
        // load — the page renders as unstyled HTML.
        //
        // The isSecure() check honors trusted proxies (Laravel 11+ trusts '*'
        // by default), so direct-HTTPS works too. The raw X-Forwarded-Proto
        // fallback covers the case where TrustProxies isn't active, so this
        // is belt-and-braces rather than relying on a single mechanism.
        //
        // Local dev (php artisan serve on http://localhost:8000) is unaffected:
        // the request is not secure and no proxy header is present, so no
        // force is applied and asset() emits http:// URLs as expected.
        $request = request();

        $behindHttpsProxy = $request
            && ($request->isSecure()
                || $request->server('HTTP_X_FORWARDED_PROTO') === 'https');

        if ($behindHttpsProxy) {
            URL::forceScheme('https');
        }
    }
}
