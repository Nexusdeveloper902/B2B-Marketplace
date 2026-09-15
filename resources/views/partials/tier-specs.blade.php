@php
    // Scope counts per package — the same published numbers the landing
    // widget animates (public/js/app.js PACKAGE_DATA). $i = tier index.
    $es = app()->getLocale() === 'es';
    $specs = [
        ['readers' => '1', 'cards' => '200', 'apps' => '1'],
        ['readers' => '2–10', 'cards' => $es ? '2.000' : '2,000', 'apps' => '3'],
        ['readers' => '∞', 'cards' => '∞', 'apps' => '3+'],
    ][$i];
@endphp
<dl class="spec-chips">
    <div><dt>{{ __('landing.widget.readers') }}</dt><dd>{{ $specs['readers'] }}</dd></div>
    <div><dt>{{ __('landing.widget.cards') }}</dt><dd>{{ $specs['cards'] }}</dd></div>
    <div><dt>{{ __('landing.widget.apps') }}</dt><dd>{{ $specs['apps'] }}</dd></div>
</dl>
