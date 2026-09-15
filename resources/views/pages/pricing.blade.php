@extends('layouts.app')

@section('page', 'pricing')
@section('title', __('pricing.meta_title'))
@section('description', __('pricing.meta_description'))

@php
    $tiers = __('pricing.tiers');
    $tierKeys = ['starter', 'campus', 'enterprise'];
    $f = fn ($t, $n) => $tiers[$t]['features'][$n];
    // Comparison matrix: every cell is an existing feature line (or a
    // check where a tier's list states it — Campus = "Everything in
    // Starter"; Enterprise lists 3+ applications in the published counts).
    $yes = ['check' => true];
    $no = ['none' => true];
    $compare = [
        'price' => [$tiers[0]['price'], $tiers[1]['price'], $tiers[2]['price']],
        'readers' => [$f(0, 0), $f(1, 1), $f(2, 1)],
        'cards' => [$f(0, 1), $f(1, 2), $f(2, 1)],
        'attendance' => [$f(0, 2), $f(1, 0), $yes],
        'meals' => [$no, $f(1, 3), $yes],
        'recycling' => [$no, $f(1, 4), $yes],
        'custom' => [$no, $no, $f(2, 2)],
        'api' => [$no, $no, $f(2, 3)],
        'reports' => [$f(0, 3), $f(1, 5), $f(2, 4)],
        'onboarding' => [$f(0, 5), $f(1, 0), $f(2, 6)],
        'support' => [$f(0, 6), $f(1, 6), $f(2, 5)],
    ];
@endphp

@section('content')
    <section class="store-hero">
        <div class="shell">
            @include('partials.breadcrumb', ['current' => __('nav.pricing')])
            <div class="store-hero-grid">
                <div>
                    <h1>{{ __('pricing.hero.title') }}</h1>
                    <p>{{ __('pricing.hero.body') }}</p>
                </div>
                <ol class="arch-pipeline arch-pipeline-hero" aria-hidden="true">
                    @foreach (__('pricing.strip.pipeline') as $token)
                        <li>{{ $token }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ============ Package listings ============ --}}
    <section class="section section-flush">
        <div class="shell">
            <div class="tiers">
                @foreach ($tiers as $i => $tier)
                    <section @class(['tier', 'tier-featured' => $i === 1]) id="tier-{{ $tierKeys[$i] }}">
                        <header class="tier-head">
                            <p class="tier-audience">{{ $tier['audience'] }}</p>
                            <h2>{{ $tier['name'] }}</h2>
                        </header>
                        <div class="tier-body">
                            @include('partials.tier-specs', ['i' => $i])
                            <p class="tier-price">{{ $tier['price'] }}</p>
                            <p class="tier-footnote">{{ $tier['footnote'] }}</p>
                            <ul class="tier-features">
                                @foreach ($tier['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <div class="tier-actions">
                                <a class="btn tier-cta" href="{{ route('contact.show', ['tier' => $tierKeys[$i]]) }}">{{ $tier['cta'] }}</a>
                                @include('partials.quote-add', ['key' => 'pkg:'.$tierKeys[$i], 'class' => 'quote-add-block'])
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Comparison ============ --}}
    <section class="section section-tight" id="compare">
        <div class="shell">
            <div class="shelf-head">
                <div>
                    <h2>{{ __('store.compare.title') }}</h2>
                    <p>{{ __('store.compare.intro') }}</p>
                </div>
            </div>
            <div class="compare-wrap" tabindex="0">
                <table class="compare">
                    <thead>
                        <tr>
                            <th scope="col"><span class="sr-only">{{ __('store.compare.feature') }}</span></th>
                            @foreach ($tiers as $i => $tier)
                                <th scope="col" @class(['is-featured' => $i === 1])>
                                    <span class="compare-name">{{ $tier['name'] }}</span>
                                    <span class="compare-aud">{{ $tier['audience'] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compare as $row => $cells)
                            <tr>
                                <th scope="row">{{ __('store.compare.rows.'.$row) }}</th>
                                @foreach ($cells as $i => $cell)
                                    <td @class(['is-featured' => $i === 1])>
                                        @if (is_array($cell) && isset($cell['check']))
                                            <span class="mark mark-yes"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><span class="sr-only">{{ __('store.compare.included') }}</span></span>
                                        @elseif (is_array($cell))
                                            <span class="mark mark-no" aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('store.compare.not_listed') }}</span>
                                        @else
                                            {{ $cell }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="compare-actions">
                            <th scope="row"><span class="sr-only">{{ __('store.quote.cta') }}</span></th>
                            @foreach ($tiers as $i => $tier)
                                <td @class(['is-featured' => $i === 1])>
                                    <a class="btn btn-quiet btn-topbar" href="{{ route('contact.show', ['tier' => $tierKeys[$i]]) }}">{{ $tier['cta'] }}</a>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ============ Same architecture strip ============ --}}
    <section class="closing">
        <div class="shell closing-in">
            <div>
                <h2>{{ __('pricing.strip.title') }}</h2>
                <p>{{ __('pricing.strip.body') }}</p>
            </div>
            <ol class="arch-pipeline arch-pipeline-invert" aria-hidden="true">
                @foreach (__('pricing.strip.pipeline') as $token)
                    <li>{{ $token }}</li>
                @endforeach
            </ol>
        </div>
    </section>
@endsection
