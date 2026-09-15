@extends('layouts.app')

@section('page', 'product')
@section('title', __('product.meta_title'))
@section('description', __('product.meta_description'))

@php
    $tiers = __('pricing.tiers');
    $tierKeys = ['starter', 'campus', 'enterprise'];
    $appKeys = ['attendance', 'meals', 'recycling'];
    $appTiers = [[0, 1, 2], [1, 2], [1, 2]];
@endphp

@section('content')
    <section class="pdp">
        <div class="shell">
            @include('partials.breadcrumb', ['current' => __('nav.product')])

            <div class="pdp-grid">
                {{-- Gallery: the four stations of one event --}}
                <div class="pdp-gallery">
                    <figure class="pdp-stage" role="img" aria-label="{{ __('store.pdp.gallery_aria') }}">
                        <div class="feature-glow" aria-hidden="true"></div>
                        <div class="pdp-scene" aria-hidden="true">
                            <div class="art-card">
                                <span class="art-card-chip"></span>
                                <span class="art-card-waves"><i></i><i></i><i></i></span>
                                <span class="art-card-id">{{ __('landing.ledger.card_label') }} 0441</span>
                            </div>
                            <div class="art-reader">
                                <span class="art-reader-light"></span>
                                <span class="art-reader-name">GATE-A</span>
                            </div>
                        </div>
                        <pre class="pdp-event" aria-hidden="true"><code>{
  <span class="v">"card"</span>: "0441",
  <span class="v">"reader"</span>: "GATE-A",
  <span class="v">"at"</span>: "07:58:12",
  <span class="v">"type"</span>: "attendance.in"
}</code></pre>
                    </figure>
                    <ol class="pdp-thumbs">
                        @foreach (__('product.pipeline.blocks') as $n => $block)
                            <li>
                                <a href="#pipeline">
                                    <span class="pdp-thumb-n">0{{ $n + 1 }}</span>
                                    <span class="pdp-thumb-label">{{ $block['label'] }}</span>
                                    <span class="pdp-thumb-title">{{ $block['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>

                {{-- Buy box: pick a package, go to the quote form preselected (works without JS) --}}
                <aside class="buybox">
                    <p class="hero-kicker"><i aria-hidden="true"></i>{{ __('landing.hero.kicker') }}</p>
                    <h1>{{ __('product.hero.headline') }}</h1>
                    <p class="buybox-body">{{ __('product.hero.body') }}</p>

                    <div class="buybox-block">
                        <p class="buybox-label">{{ __('store.pdp.apps') }}</p>
                        <ul class="label-chips">
                            @foreach (__('product.apps.items') as $app)
                                <li>{{ $app['label'] }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <form class="buybox-form" method="GET" action="{{ route('contact.show') }}">
                        <fieldset>
                            <legend class="buybox-label">{{ __('store.pdp.choose') }}</legend>
                            @foreach ($tiers as $i => $tier)
                                <label class="option">
                                    <input type="radio" name="tier" value="{{ $tierKeys[$i] }}" @checked($i === 1)>
                                    <span class="option-box">
                                        <span class="option-name">{{ $tier['name'] }} <small>{{ $tier['audience'] }}</small></span>
                                        <span class="option-price">{{ $tier['price'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>
                        <div class="buybox-actions">
                            <button type="submit" class="btn btn-primary">{{ __('store.quote.cta') }}</button>
                            @include('partials.quote-add', ['key' => 'pkg:campus', 'class' => 'quote-add-block', 'follow' => 'tier'])
                        </div>
                    </form>

                    <div class="buybox-block">
                        <p class="buybox-label">{{ __('store.pdp.fields') }}</p>
                        <ul class="field-row">
                            @foreach (__('product.anatomy.fields') as $field)
                                <li><code>{{ $field['key'] }}</code><span>{{ $field['title'] }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <nav class="subnav" aria-label="{{ __('store.pdp.tabs_aria') }}">
        <div class="shell subnav-in">
            <a href="#pipeline">{{ __('product.pipeline.title') }}</a>
            <a href="#anatomy">{{ __('product.anatomy.title') }}</a>
            <a href="#apps">{{ __('product.apps.title') }}</a>
        </div>
    </nav>

    {{-- ============ Pipeline diagram ============ --}}
    <section class="section" id="pipeline">
        <div class="shell">
            <div class="shelf-head" data-reveal>
                <div>
                    <h2>{{ __('product.pipeline.title') }}</h2>
                    <p>{{ __('product.pipeline.intro') }}</p>
                </div>
            </div>

            <div class="flow" role="img" aria-label="{{ __('product.pipeline.title') }}" data-reveal-stagger>
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
    <section class="section section-wash" id="anatomy">
        <div class="shell section-grid">
            <div class="section-head" data-reveal>
                <h2>{{ __('product.anatomy.title') }}</h2>
                <p>{{ __('product.anatomy.intro') }}</p>
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
            <div class="section-body">
                <dl class="anatomy" data-reveal>
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
            </div>
        </div>
    </section>

    {{-- ============ Applications ============ --}}
    <section class="section" id="apps">
        <div class="shell">
            <div class="shelf-head" data-reveal>
                <div>
                    <h2>{{ __('product.apps.title') }}</h2>
                    <p>{{ __('product.apps.intro') }}</p>
                </div>
            </div>
            <div class="apps" data-reveal-stagger>
                @foreach (__('product.apps.items') as $i => $app)
                    <article class="app">
                        <p class="app-label">{{ $app['label'] }}</p>
                        <h3>{{ $app['title'] }}</h3>
                        <p>{{ $app['body'] }}</p>
                        @include('partials.module-foot', ['tiersIn' => $appTiers[$i], 'key' => 'app:'.$appKeys[$i]])
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
                <a class="btn btn-gold" href="{{ route('enterprise') }}">{{ __('product.note.link') }}</a>
            </div>
        </div>
    </section>
@endsection
