<footer class="footer">
    <div class="shell">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="wordmark wordmark-footer" href="{{ route('landing') }}">
                    <img class="wordmark-mark" src="{{ asset('brand/mark-cream-96.png') }}" alt="" width="42" height="30">
                    <span class="wordmark-name">{{ __('common.wordmark') }}</span>
                </a>
                <p class="footer-note">{{ __('common.footer.note') }}</p>
            </div>

            <nav class="footer-col" aria-label="{{ __('store.categories.title') }}">
                <h2 class="footer-heading">{{ __('store.categories.title') }}</h2>
                <a href="{{ route('pricing') }}">{{ __('store.categories.packages') }}</a>
                <a href="{{ route('landing') }}#applications">{{ __('store.categories.apps') }}</a>
                <a href="{{ route('enterprise') }}">{{ __('store.categories.cases') }}</a>
                <a href="{{ route('product') }}">{{ __('store.categories.how') }}</a>
            </nav>

            <nav class="footer-col" aria-label="{{ __('common.footer.site') }}">
                <h2 class="footer-heading">{{ __('common.footer.site') }}</h2>
                <a href="{{ route('product') }}">{{ __('nav.product') }}</a>
                <a href="{{ route('pricing') }}">{{ __('nav.pricing') }}</a>
                <a href="{{ route('enterprise') }}">{{ __('nav.enterprise') }}</a>
                <a href="{{ route('contact.show') }}">{{ __('nav.contact') }}</a>
            </nav>

            <nav class="footer-col" aria-label="{{ __('common.footer.get_started') }}">
                <h2 class="footer-heading">{{ __('common.footer.get_started') }}</h2>
                <a href="{{ route('pricing') }}">{{ __('common.footer.packages') }}</a>
                <a href="{{ route('contact.show') }}">{{ __('common.footer.demo') }}</a>
            </nav>
        </div>

        <div class="footer-legal">
            <p>&copy; {{ __('common.footer.copyright') }}</p>
            <p>{{ __('common.footer.built') }}</p>
        </div>
    </div>
</footer>
