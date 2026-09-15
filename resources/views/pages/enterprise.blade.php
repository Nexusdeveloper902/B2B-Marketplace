@extends('layouts.app')

@section('page', 'enterprise')
@section('title', __('enterprise.meta_title'))
@section('description', __('enterprise.meta_description'))

@php $caseKeys = ['shift', 'zone', 'asset', 'visitor']; @endphp

@section('content')
    <section class="store-hero store-hero-dark">
        <div class="feature-glow" aria-hidden="true"></div>
        <div class="shell">
            @include('partials.breadcrumb', ['current' => __('nav.enterprise')])
            <div class="ent-hero-grid">
                <div>
                    <h1>{{ __('enterprise.hero.title') }}</h1>
                    <p>{{ __('enterprise.hero.body') }}</p>
                </div>

                <aside class="buybox buybox-compact">
                    <p class="buybox-label">Enterprise &middot; {{ __('pricing.tiers.2.price') }}</p>
                    <h2>{{ __('enterprise.includes.title') }}</h2>
                    <ul class="checklist">
                        @foreach (__('enterprise.includes.items') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <div class="buybox-actions">
                        <a class="btn btn-primary" href="{{ route('contact.show', ['tier' => 'enterprise']) }}">{{ __('enterprise.includes.link') }}</a>
                        @include('partials.quote-add', ['key' => 'pkg:enterprise', 'class' => 'quote-add-block'])
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- ============ Pattern ============ --}}
    <section class="section">
        <div class="shell section-grid">
            <div class="section-head" data-reveal>
                <h2>{{ __('enterprise.pattern.title') }}</h2>
                <p>{{ __('enterprise.pattern.intro') }}</p>
            </div>
            <div class="section-body">
                <dl class="anatomy" data-reveal>
                    @foreach (__('enterprise.pattern.fields') as $field)
                        <div class="anatomy-row">
                            <dt>
                                <span class="anatomy-key">{{ $field['key'] }}</span>
                                <span class="anatomy-title">{{ $field['title'] }}</span>
                            </dt>
                            <dd>{{ $field['body'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- ============ Use cases — catalog ============ --}}
    <section class="section section-wash">
        <div class="shell">
            <div class="shelf-head" data-reveal>
                <div>
                    <h2>{{ __('enterprise.cases.title') }}</h2>
                    <p>{{ __('enterprise.cases.intro') }}</p>
                </div>
            </div>

            <div class="cases" data-reveal-stagger>
                @foreach (__('enterprise.cases.items') as $i => $case)
                    <article class="case">
                        <div class="case-event">
                            <table class="event-record" aria-label="{{ $case['title'] }}">
                                <tbody>
                                    <tr><th scope="row">{{ __('landing.ledger.columns.2') }}</th><td>{{ $case['reader'] }}</td></tr>
                                    <tr><th scope="row">{{ __('landing.ledger.columns.1') }}</th><td>{{ $case['card'] }}</td></tr>
                                    <tr><th scope="row">{{ __('landing.ledger.columns.0') }}</th><td>{{ $case['time'] }}</td></tr>
                                    <tr><th scope="row">{{ __('landing.ledger.columns.3') }}</th><td>{{ $case['type'] }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="case-copy">
                            <h3>{{ $case['title'] }}</h3>
                            <p>{{ $case['body'] }}</p>
                            @include('partials.quote-add', ['key' => 'case:'.$caseKeys[$i]])
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="closing">
        <div class="shell closing-in">
            <div>
                <h2>{{ __('enterprise.pattern.intro') }}</h2>
                <p>{{ __('pricing.tiers.2.footnote') }}</p>
            </div>
            <div class="closing-actions">
                <a class="btn btn-gold" href="{{ route('contact.show', ['tier' => 'enterprise']) }}">{{ __('pricing.tiers.2.cta') }}</a>
            </div>
        </div>
    </section>
@endsection
