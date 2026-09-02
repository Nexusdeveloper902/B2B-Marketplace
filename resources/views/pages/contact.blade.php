@extends('layouts.app')

@section('title', __('contact.meta_title'))

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="page-hero">
        <div class="shell">
            <h1>{{ __('contact.hero.title') }}</h1>
            <p>{{ __('contact.hero.body') }}</p>
        </div>
    </section>

    {{-- ============ Form + next steps ============ --}}
    <section class="section section-flush">
        <div class="shell contact-grid">

            <div class="contact-aside">
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

            <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
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

                <div class="field">
                    <label for="name">{{ __('contact.form.name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
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
                        autocomplete="email"
                        @class(['is-invalid' => $errors->has('email')])
                    >
                    @error('email')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="organization">{{ __('contact.form.organization') }}</label>
                    <input
                        type="text"
                        id="organization"
                        name="organization"
                        value="{{ old('organization') }}"
                        required
                        autocomplete="organization"
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
                        <option value="" disabled @selected(old('tier') === null || old('tier') === '')>
                            {{ __('contact.form.tier_placeholder') }}
                        </option>
                        @foreach (__('contact.form.tier_options') as $value => $label)
                            <option value="{{ $value }}" @selected(old('tier') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('tier')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="message">{{ __('contact.form.message') }}</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="5"
                        required
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
        </div>
    </section>
@endsection
