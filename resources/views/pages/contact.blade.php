@extends('layouts.app')

@section('page', 'contact')
@section('title', __('contact.meta_title'))
@section('description', __('contact.meta_description'))

@php
    // A package chosen on the catalog arrives as ?tier=… and preselects the
    // field; a failed submission's old() input always wins. Unknown values
    // are ignored (the store() validator is still the only gate).
    $tierOptions = __('contact.form.tier_options');
    $requested = request()->query('tier');
    $selectedTier = old('tier', is_string($requested) && array_key_exists($requested, $tierOptions) ? $requested : null);
@endphp

@section('content')
    <section class="store-hero">
        <div class="shell">
            @include('partials.breadcrumb', ['current' => __('nav.contact')])
            <div class="store-hero-grid">
                <div>
                    <h1>{{ __('contact.hero.title') }}</h1>
                    <p>{{ __('contact.hero.body') }}</p>
                </div>
            </div>
            <ol class="stepper" aria-label="{{ __('contact.next.title') }}">
                @foreach (__('contact.next.steps') as $n => $step)
                    <li @class(['is-current' => $n === 0])><span class="stepper-n">{{ $n + 1 }}</span>{{ $step['title'] }}</li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ Form + order summary ============ --}}
    <section class="section section-flush">
        <div class="shell contact-grid">

            <form class="contact-form" method="POST" action="{{ route('contact.store') }}" autocomplete="off">
                @csrf

                @if ($errors->any())
                    <div class="form-alert" role="alert">
                        <p>{{ __('contact.form.aria_errors') }}</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="field-pair">
                    <div class="field">
                        <label for="name">{{ __('contact.form.name') }}</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="off"
                            @class(['is-invalid' => $errors->has('name')])
                        >
                        @error('name')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="email">{{ __('contact.form.email') }}</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="off"
                            @class(['is-invalid' => $errors->has('email')])
                        >
                        @error('email')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="field">
                    <label for="organization">{{ __('contact.form.organization') }}</label>
                    <input
                        type="text"
                        id="organization"
                        name="organization"
                        value="{{ old('organization') }}"
                        required
                        autocomplete="off"
                        @class(['is-invalid' => $errors->has('organization')])
                    >
                    @error('organization')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="tier">{{ __('contact.form.tier') }}</label>
                    <select
                        id="tier"
                        name="tier"
                        required
                        @class(['is-invalid' => $errors->has('tier')])
                    >
                        <option value="" disabled @selected($selectedTier === null || $selectedTier === '')>
                            {{ __('contact.form.tier_placeholder') }}
                        </option>
                        @foreach ($tierOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedTier === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('tier')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="message">{{ __('contact.form.message') }}</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        required
                        data-quote-message
                        @class(['is-invalid' => $errors->has('message')])
                    >{{ old('message') }}</textarea>
                    <p class="field-hint">{{ __('contact.form.message_hint') }}</p>
                    @error('message')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">{{ __('contact.form.submit') }}</button>
                    <p class="form-privacy">{{ __('contact.form.privacy') }}</p>
                </div>
            </form>

            <aside class="contact-aside">
                {{-- Quote-list summary: filled by store.js; stays hidden without JS. --}}
                <div class="summary" data-quote-summary hidden>
                    <h2>{{ __('store.quote.title') }}</h2>
                    <ul class="quote-items quote-items-compact" data-quote-list></ul>
                    <p class="summary-empty" data-quote-empty>{{ __('store.quote.summary_empty') }}</p>
                    <p class="summary-note">{{ __('store.quote.note') }}</p>
                </div>

                <div class="next-card">
                    <h2>{{ __('contact.next.title') }}</h2>
                    <ol class="next-steps">
                        @foreach (__('contact.next.steps') as $step)
                            <li>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['body'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </aside>
        </div>
    </section>
@endsection
