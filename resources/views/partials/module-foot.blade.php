@php $names = array_column(__('pricing.tiers'), 'name'); @endphp
<div class="module-foot">
    <span class="included">{{ __('store.included_in') }}
        @foreach ($tiersIn as $t)<b>{{ $names[$t] }}</b>@endforeach
    </span>
    @include('partials.quote-add', ['key' => $key])
</div>
