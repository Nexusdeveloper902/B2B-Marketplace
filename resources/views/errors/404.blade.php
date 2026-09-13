@extends('layouts.app')

@section('title', __('common.not_found_title'))

@section('content')
<section class="section">
    <div class="shell nf-wrap" data-reveal>
        <p class="nf-code">404</p>
        <h1>{{ __('common.not_found_title') }}</h1>
        <p class="nf-text">{{ __('common.not_found_body') }}</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="{{ route('landing') }}">{{ __('common.not_found_cta') }}</a>
        </div>
    </div>
</section>
@endsection
