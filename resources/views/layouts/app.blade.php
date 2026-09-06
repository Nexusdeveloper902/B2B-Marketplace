<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('common.title_default'))</title>
    <meta name="description" content="@yield('description', __('common.description_default'))">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
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
</head>
<body data-page="@yield('page', 'default')">
    <a class="skip" href="#main">{{ __('nav.skip') }}</a>

    @include('partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
