{{-- Add-to-quote toggle. Rendered hidden: store.js reveals it, so a no-JS
     visitor never sees a button that does nothing (honesty floor). --}}
<button type="button" class="quote-add{{ isset($class) ? ' '.$class : '' }}" data-quote-add="{{ $key }}" aria-pressed="false"@isset($follow) data-quote-follow="{{ $follow }}"@endisset hidden>
    <svg class="quote-add-plus" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    <svg class="quote-add-check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
    <span data-quote-add-label>{{ __('store.quote.add') }}</span>
</button>
