{{-- Utility strip: who it's for + language (marketplace layer, TASK-014) --}}
<div class="utilbar">
    <div class="shell utilbar-in">
        <p class="utilbar-note"><i aria-hidden="true"></i>{{ __('landing.hero.kicker') }} <span aria-hidden="true">&middot;</span> {{ __('common.footer.built') }}</p>
        <nav class="langswitch" aria-label="{{ __('nav.lang_toggle') }}">
            <a href="{{ route('locale.switch', 'en') }}" @class(['is-active' => app()->getLocale() === 'en']) @if (app()->getLocale() === 'en') aria-current="true" @endif>EN</a>
            <span class="langswitch-sep" aria-hidden="true">/</span>
            <a href="{{ route('locale.switch', 'es') }}" @class(['is-active' => app()->getLocale() === 'es']) @if (app()->getLocale() === 'es') aria-current="true" @endif>ES</a>
        </nav>
    </div>
</div>

<header class="topbar">
    <div class="shell topbar-in">
        <a class="wordmark" href="{{ route('landing') }}" aria-label="{{ __('common.wordmark') }}">
            <img class="wordmark-mark" src="{{ asset('brand/mark-96.png') }}" alt="" width="42" height="30">
            <span class="wordmark-name">{{ __('common.wordmark') }}</span>
        </a>

        <nav class="topnav" aria-label="{{ __('nav.primary') }}">
            <a href="{{ route('product') }}" @class(['is-active' => request()->routeIs('product')])>{{ __('nav.product') }}</a>
            <a href="{{ route('pricing') }}" @class(['is-active' => request()->routeIs('pricing')])>{{ __('nav.pricing') }}</a>
            <a href="{{ route('enterprise') }}" @class(['is-active' => request()->routeIs('enterprise')])>{{ __('nav.enterprise') }}</a>
            <a href="{{ route('contact.show') }}" @class(['is-active' => request()->routeIs('contact.*') && ! request()->routeIs('contact.thankYou')])>{{ __('nav.contact') }}</a>
        </nav>

        <div class="topbar-tools">
            {{-- No-JS: a plain link to the request form. With JS it opens the
                 quote-list drawer instead (store.js). --}}
            <a class="quote-btn" href="{{ route('contact.show') }}" data-quote-open>
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M8 6h12M8 12h12M8 18h12"/><circle cx="3.5" cy="6" r="1.2" fill="currentColor" stroke="none"/><circle cx="3.5" cy="12" r="1.2" fill="currentColor" stroke="none"/><circle cx="3.5" cy="18" r="1.2" fill="currentColor" stroke="none"/></svg>
                <span class="quote-btn-label">{{ __('store.quote.button') }}</span>
                <span class="quote-count" data-quote-count hidden>0<span class="sr-only"> {{ __('store.quote.count_aria') }}</span></span>
            </a>
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
                <a class="btn btn-quiet" href="{{ route('contact.show') }}" data-quote-open>{{ __('store.quote.button') }} <span class="quote-count" data-quote-count hidden>0</span></a>
                <a class="btn btn-primary" href="{{ route('contact.show') }}">{{ __('nav.cta') }}</a>
            </div>
        </details>
    </div>
</header>
