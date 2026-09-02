@extends('layouts.app')

@section('title', __('enterprise.meta_title'))
@section('description', __('enterprise.meta_description'))

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="page-hero">
        <div class="shell">
            <h1>{{ __('enterprise.hero.title') }}</h1>
            <p>{{ __('enterprise.hero.body') }}</p>
        </div>
    </section>

    {{-- ============ Pattern ============ --}}
    <section class="section">
        <div class="shell section-grid">
            <div class="section-head">
                <h2>{{ __('enterprise.pattern.title') }}</h2>
                <p>{{ __('enterprise.pattern.intro') }}</p>
            </div>
            <div class="section-body">
                <dl class="anatomy">
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

    {{-- ============ Use cases ============ --}}
    <section class="section section-wash">
        <div class="shell">
            <div class="section-head-wide">
                <h2>{{ __('enterprise.cases.title') }}</h2>
                <p>{{ __('enterprise.cases.intro') }}</p>
            </div>

            <div class="cases">
                @foreach (__('enterprise.cases.items') as $case)
                    <article class="case">
                        <div class="case-copy">
                            <h3>{{ $case['title'] }}</h3>
                            <p>{{ $case['body'] }}</p>
                        </div>
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
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Includes + CTA ============ --}}
    <section class="section">
        <div class="shell section-grid">
            <div class="section-head">
                <h2>{{ __('enterprise.includes.title') }}</h2>
            </div>
            <div class="section-body">
                <ul class="checklist">
                    @foreach (__('enterprise.includes.items') as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <a class="btn btn-primary" href="{{ route('contact.show') }}">{{ __('enterprise.includes.link') }}</a>
            </div>
        </div>
    </section>
@endsection
