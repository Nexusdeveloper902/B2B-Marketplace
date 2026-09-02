@extends('layouts.app')

@section('title', __('landing.meta_title'))
@section('description', __('landing.meta_description'))

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="hero">
        <div class="shell hero-grid">
            <div class="hero-copy">
                <h1>{{ __('landing.hero.headline') }}</h1>
                <p class="hero-pitch">{{ __('landing.hero.pitch') }}</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ route('product') }}">{{ __('landing.hero.cta_primary') }}</a>
                    <a class="btn btn-quiet" href="{{ route('contact.show') }}">{{ __('landing.hero.cta_secondary') }}</a>
                </div>
            </div>

            <figure class="ledger" role="img" aria-label="{{ __('landing.ledger.aria') }}">
                <figcaption class="ledger-cap">
                    <span class="ledger-dot" aria-hidden="true"></span>
                    <span class="ledger-title">{{ __('landing.ledger.title') }}</span>
                    <span class="ledger-live">{{ __('landing.ledger.live') }}</span>
                </figcaption>

                <div class="tap-visual" aria-hidden="true">
                    <div class="tap-card">
                        <span class="tap-card-chip" aria-hidden="true"></span>
                        <span class="tap-card-id">{{ __('landing.ledger.card_label') }} 0441</span>
                    </div>
                    <div class="tap-target">
                        <span class="tap-light" aria-hidden="true"></span>
                        <span class="tap-wave" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span class="tap-reader-name">GATE-A</span>
                    </div>
                </div>

                <table class="ledger-table">
                    <thead>
                        <tr>
                            @foreach (__('landing.ledger.columns') as $column)
                                <th scope="col">{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="is-new">
                            <td>07:58:12</td><td>0441</td><td>GATE-A</td><td>attendance.in</td>
                        </tr>
                        <tr><td>07:58:14</td><td>0087</td><td>GATE-A</td><td>attendance.in</td></tr>
                        <tr><td>08:02:47</td><td>0132</td><td>GATE-B</td><td>attendance.in</td></tr>
                        <tr><td>12:14:03</td><td>0441</td><td>PAE-1</td><td>meal.lunch</td></tr>
                        <tr><td>15:41:09</td><td>0558</td><td>ECO-PT</td><td>recycle.drop</td></tr>
                        <tr><td>16:22:41</td><td>0132</td><td>GATE-B</td><td>attendance.out</td></tr>
                    </tbody>
                </table>
            </figure>
        </div>
    </section>

    {{-- ============ Problem ============ --}}
    <section class="section">
        <div class="shell section-grid">
            <div class="section-head">
                <h2>{{ __('landing.problem.title') }}</h2>
            </div>
            <div class="section-body">
                <p>{{ __('landing.problem.body_1') }}</p>
                <p>{{ __('landing.problem.body_2') }}</p>

                <div class="costs">
                    <h3>{{ __('landing.problem.costs_title') }}</h3>
                    <ul class="costs-list">
                        @foreach (__('landing.problem.costs') as $cost)
                            <li>{{ $cost }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Steps ============ --}}
    <section class="section section-wash">
        <div class="shell">
            <div class="section-head-wide">
                <h2>{{ __('landing.steps.title') }}</h2>
                <p>{{ __('landing.steps.intro') }}</p>
            </div>
            <ol class="steps">
                @foreach (__('landing.steps.items') as $index => $step)
                    <li class="step">
                        <span class="step-num" aria-hidden="true">{{ $loop->iteration }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ Applications ============ --}}
    <section class="section">
        <div class="shell">
            <div class="section-head-wide">
                <h2>{{ __('landing.apps.title') }}</h2>
                <p>{{ __('landing.apps.intro') }}</p>
            </div>
            <div class="apps">
                @foreach (__('landing.apps.items') as $app)
                    <article class="app">
                        <p class="app-label">{{ $app['label'] }}</p>
                        <h3>{{ $app['title'] }}</h3>
                        <p>{{ $app['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Audience ============ --}}
    <section class="section section-wash">
        <div class="shell section-grid">
            <div class="section-head">
                <h2>{{ __('landing.audience.title') }}</h2>
            </div>
            <div class="section-body">
                @foreach (__('landing.audience.items') as $item)
                    <div class="audience-row">
                        <div class="audience-copy">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['body'] }}</p>
                        </div>
                        <a class="audience-link" href="{{ route($item['href']) }}">{{ $item['link'] }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Closing CTA ============ --}}
    <section class="closing">
        <div class="shell closing-in">
            <div>
                <h2>{{ __('landing.closing.title') }}</h2>
                <p>{{ __('landing.closing.body') }}</p>
            </div>
            <div class="closing-actions">
                <a class="btn btn-primary" href="{{ route('product') }}">{{ __('landing.closing.cta_primary') }}</a>
                <a class="btn btn-quiet btn-quiet-invert" href="{{ route('contact.show') }}">{{ __('landing.closing.cta_secondary') }}</a>
            </div>
        </div>
    </section>
@endsection
