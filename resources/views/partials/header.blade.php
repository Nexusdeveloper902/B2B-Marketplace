<header class="topbar">
    <div class="shell topbar-in">
        <a class="wordmark" href="{{ route('landing') }}" aria-label="{{ __('common.wordmark') }}">
            <span class="wordmark-tap" aria-hidden="true"></span>
            <span class="wordmark-name">Presence<em>Platform</em></span>
        </a>

        <nav class="topnav" aria-label="{{ __('nav.primary') }}">
            <a href="{{ route('product') }}" @class(['is-active' => request()->routeIs('product')])>{{ __('nav.product') }}</a>
            <a href="{{ route('pricing') }}" @class(['is-active' => request()->routeIs('pricing')])>{{ __('nav.pricing') }}</a>
            <a href="{{ route('enterprise') }}" @class(['is-active' => request()->routeIs('enterprise')])>{{ __('nav.enterprise') }}</a>
            <a href="{{ route('contact.show') }}" @class(['is-active' => request()->routeIs('contact.*') && ! request()->routeIs('contact.thankYou')])>{{ __('nav.contact') }}</a>
        </nav>

        <div class="topbar-tools">
            <nav class="langswitch" aria-label="{{ __('nav.lang_toggle') }}">
                <a href="{{ route('locale.switch', 'en') }}" @class(['is-active' => app()->getLocale() === 'en']) @if (app()->getLocale() === 'en') aria-current="true" @endif>EN</a>
                <span class="langswitch-sep" aria-hidden="true">/</span>
                <a href="{{ route('locale.switch', 'es') }}" @class(['is-active' => app()->getLocale() === 'es']) @if (app()->getLocale() === 'es') aria-current="true" @endif>ES</a>
            </nav>
            <a class="btn btn-primary btn-topbar" href="{{ route('contact.show') }}">{{ __('nav.cta') }}</a>
        </div>

        <details class="mobilenav">
            <summary aria-label="{{ __('nav.menu') }}">
                <span class="mobilenav-bars" aria-hidden="true"><i></i><i></i><i></i></span>
                <span class="mobilenav-label">{{ __('nav.menu') }}</span>
            </summary>
            <div class="mobilenav-body">
                <nav class="mobilenav-links" aria-label="{{ __('nav.menu') }}">
                    <a href="{{ route('product') }}">{{ __('nav.product') }}</a>
                    <a href="{{ route('pricing') }}">{{ __('nav.pricing') }}</a>
                    <a href="{{ route('enterprise') }}">{{ __('nav.enterprise') }}</a>
                    <a href="{{ route('contact.show') }}">{{ __('nav.contact') }}</a>
                </nav>
                <a class="btn btn-primary" href="{{ route('contact.show') }}">{{ __('nav.cta') }}</a>
            </div>
        </details>
    </div>
</header>
