@extends('layouts.app')

@section('title', __('pricing.meta_title'))
@section('description', __('pricing.meta_description'))

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="page-hero">
        <div class="shell">
            <h1>{{ __('pricing.hero.title') }}</h1>
            <p>{{ __('pricing.hero.body') }}</p>
        </div>
    </section>

    {{-- ============ Tiers ============ --}}
    <section class="section section-flush">
        <div class="shell">
            <div class="tiers">
                @foreach (__('pricing.tiers') as $tier)
                    <section class="tier{{ $tier['name'] === 'Campus' ? ' tier-featured' : '' }}">
                        <header class="tier-head">
                            <h2>{{ $tier['name'] }}</h2>
                            <p>{{ $tier['audience'] }}</p>
                        </header>
                        <div class="tier-body">
                            <p class="tier-price">{{ $tier['price'] }}</p>
                            <ul class="tier-features">
                                @foreach ($tier['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <p class="tier-footnote">{{ $tier['footnote'] }}</p>
                            <a class="btn btn-quiet tier-cta" href="{{ route('contact.show') }}">{{ $tier['cta'] }}</a>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Same architecture strip ============ --}}
    <section class="section section-wash">
        <div class="shell">
            <div class="arch-strip">
                <div class="arch-copy">
                    <h2>{{ __('pricing.strip.title') }}</h2>
                    <p>{{ __('pricing.strip.body') }}</p>
                </div>
                <ol class="arch-pipeline" aria-hidden="true">
                    @foreach (__('pricing.strip.pipeline') as $token)
                        <li>{{ $token }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
@endsection
