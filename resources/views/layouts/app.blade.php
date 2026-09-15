<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('common.title_default'))</title>
    <meta name="description" content="@yield('description', __('common.description_default'))">
    {{-- Pulse brand suite: real mark favicons, PWA icons, manifest --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('brand/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('brand/favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#E8EDDF">
    <meta property="og:site_name" content="{{ __('common.wordmark') }}">
    <meta property="og:image" content="{{ asset('brand/og-image.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Motion gate: flags motion availability before first paint so reveal
         states never flash. Never adds the flag under reduced-motion. --}}
    <script>
        try {
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('js-motion');
            }
        } catch (e) { /* no motion flags without JS APIs */ }
    </script>
    <script type="module" src="{{ asset('js/app.js') }}"></script>
    {{-- Quote list + shelf controls: interaction, not motion — runs under
         reduced-motion too (TASK-014). --}}
    <script type="module" src="{{ asset('js/store.js') }}"></script>
</head>
<body data-page="@yield('page', 'default')">
    <a class="skip" href="#main">{{ __('nav.skip') }}</a>

    @include('partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.quote-drawer')
</body>
</html>
