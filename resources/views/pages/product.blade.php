@extends('layouts.app')

@section('title', __('product.meta_title'))
@section('description', __('product.meta_description'))

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="page-hero">
        <div class="shell">
            <h1>{{ __('product.hero.headline') }}</h1>
            <p>{{ __('product.hero.body') }}</p>
        </div>
    </section>

    {{-- ============ Pipeline diagram ============ --}}
    <section class="section">
        <div class="shell">
            <div class="section-head-wide">
                <h2>{{ __('product.pipeline.title') }}</h2>
                <p>{{ __('product.pipeline.intro') }}</p>
            </div>

            <div class="flow" role="img" aria-label="{{ __('product.pipeline.title') }}">
                @foreach (__('product.pipeline.blocks') as $block)
                    <div class="flow-node">
                        <div class="flow-box">
                            <p class="flow-label">{{ $block['label'] }}</p>
                            <h3>{{ $block['title'] }}</h3>
                            <p>{{ $block['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Event anatomy ============ --}}
    <section class="section section-wash">
        <div class="shell section-grid">
            <div class="section-head">
                <h2>{{ __('product.anatomy.title') }}</h2>
                <p>{{ __('product.anatomy.intro') }}</p>
            </div>
            <div class="section-body">
                <dl class="anatomy">
                    @foreach (__('product.anatomy.fields') as $field)
                        <div class="anatomy-row">
                            <dt>
                                <span class="anatomy-key">{{ $field['key'] }}</span>
                                <span class="anatomy-title">{{ $field['title'] }}</span>
                            </dt>
                            <dd>{{ $field['body'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="sample-event">
                    <p class="sample-event-title">{{ __('product.anatomy.sample_title') }}</p>
                    <pre class="sample-event-code"><code>{
  <span class="v">"card"</span>: "0441",
  <span class="v">"reader"</span>: "GATE-A",
  <span class="v">"at"</span>: "07:58:12",
  <span class="v">"type"</span>: "attendance.in"
}</code></pre>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Applications ============ --}}
    <section class="section">
        <div class="shell">
            <div class="section-head-wide">
                <h2>{{ __('product.apps.title') }}</h2>
                <p>{{ __('product.apps.intro') }}</p>
            </div>
            <div class="apps">
                @foreach (__('product.apps.items') as $app)
                    <article class="app">
                        <p class="app-label">{{ $app['label'] }}</p>
                        <h3>{{ $app['title'] }}</h3>
                        <p>{{ $app['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Extensibility note ============ --}}
    <section class="closing">
        <div class="shell closing-in">
            <div>
                <h2>{{ __('product.note.title') }}</h2>
                <p>{{ __('product.note.body') }}</p>
            </div>
            <div class="closing-actions">
                <a class="btn btn-primary" href="{{ route('enterprise') }}">{{ __('product.note.link') }}</a>
            </div>
        </div>
    </section>
@endsection
