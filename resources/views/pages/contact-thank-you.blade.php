@extends('layouts.app')

@section('title', __('contact.thankyou.title'))

@section('content')
    <section class="thanks">
        <div class="shell">
            <span class="thanks-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square"><path d="M5 13l5 5L19 7"/></svg>
            </span>
            <h1>{{ __('contact.thankyou.title') }}</h1>
            <p class="thanks-body">
                {{ __('contact.thankyou.body', ['email' => '<strong>' . e(session('contact_email', __('contact.form.email'))) . '</strong>']) }}
            </p>
            <div class="thanks-actions">
                <a class="btn btn-quiet" href="{{ route('contact.show') }}">{{ __('contact.thankyou.another') }}</a>
                <a class="btn btn-primary" href="{{ route('landing') }}">{{ __('contact.thankyou.home') }}</a>
            </div>
        </div>
    </section>
@endsection
