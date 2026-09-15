@php
    // The quote-list catalog is assembled ONLY from existing copy (pricing
    // tiers, application cards, enterprise use cases) — no new product facts.
    $tierKeys = ['starter', 'campus', 'enterprise'];
    $appKeys = ['attendance', 'meals', 'recycling'];
    $caseKeys = ['shift', 'zone', 'asset', 'visitor'];
    $quoteCatalog = [];
    foreach (__('pricing.tiers') as $i => $tier) {
        $quoteCatalog['pkg:'.$tierKeys[$i]] = ['group' => 'pkg', 'title' => $tier['name'], 'meta' => $tier['audience'], 'tier' => $tierKeys[$i]];
    }
    foreach (__('landing.apps.items') as $i => $app) {
        $quoteCatalog['app:'.$appKeys[$i]] = ['group' => 'app', 'title' => $app['title'], 'meta' => $app['label']];
    }
    foreach (__('enterprise.cases.items') as $i => $case) {
        $quoteCatalog['case:'.$caseKeys[$i]] = ['group' => 'case', 'title' => $case['title'], 'meta' => $case['type']];
    }
    $quotePayload = [
        'items' => $quoteCatalog,
        'groups' => __('store.quote.groups'),
        'add' => __('store.quote.add'),
        'added' => __('store.quote.added'),
        'remove' => __('store.quote.remove'),
        'prefix' => __('store.quote.message_prefix'),
    ];
@endphp
<script type="application/json" id="quote-catalog">@json($quotePayload)</script>

<dialog class="quote-drawer" id="quote-drawer" aria-labelledby="quote-drawer-title">
    <div class="quote-drawer-in">
        <header class="quote-drawer-head">
            <h2 id="quote-drawer-title">{{ __('store.quote.title') }}</h2>
            <button type="button" class="icon-btn" data-quote-close aria-label="{{ __('store.quote.close') }}">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </header>
        <p class="quote-drawer-note">{{ __('store.quote.note') }}</p>

        <ul class="quote-items" data-quote-list></ul>
        <div class="quote-empty" data-quote-empty>
            <p>{{ __('store.quote.empty') }}</p>
            <a class="btn btn-quiet btn-topbar" href="{{ route('pricing') }}">{{ __('store.quote.browse') }}</a>
        </div>

        <footer class="quote-drawer-foot">
            <button type="button" class="link-btn" data-quote-clear>{{ __('store.quote.clear') }}</button>
            <a class="btn btn-primary" href="{{ route('contact.show') }}" data-quote-cta>{{ __('store.quote.cta') }}</a>
        </footer>
    </div>
</dialog>
