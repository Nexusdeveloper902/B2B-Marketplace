@extends('layouts.app')

@section('page', 'landing')
@section('title', __('landing.meta_title'))
@section('description', __('landing.meta_description'))

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="hero">
        <div class="shell hero-grid">
            <div class="hero-copy">
                <p class="hero-kicker"><i aria-hidden="true"></i>{{ __('landing.hero.kicker') }}</p>
                <h1 data-hero-reveal>{{ __('landing.hero.headline') }}</h1>
                <p class="hero-pitch" data-hero-reveal>{{ __('landing.hero.pitch') }}</p>

                <div class="hero-chip" role="button" tabindex="0"
                     aria-label="{{ __('landing.hero.chip_aria') }}"
                     title="{{ __('landing.hero.chip_aria') }}"
                     data-copy='{"card":"0441","reader":"GATE-A","label":"attendance.in"}'>
                    <span class="chip-prompt" data-copied="{{ __('landing.hero.chip_copied') }}">{{ __('landing.hero.chip_prompt') }}</span>
                    <span class="chip-data">{ card: <b>0441</b>, reader: <b>GATE-A</b>, label: <b>attendance.in</b> }</span>
                </div>

                <div class="hero-actions" data-hero-reveal>
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
            <div class="section-head" data-reveal>
                <h2>{{ __('landing.problem.title') }}</h2>
            </div>
            <div class="section-body" data-reveal>
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

    {{-- ============ Steps — bento: every capability is shown live ============ --}}
    <section class="section section-wash">
        <div class="shell">
            <div class="section-head-wide" data-reveal>
                <h2>{{ __('landing.steps.title') }}</h2>
                <p>{{ __('landing.steps.intro') }}</p>
            </div>

            <div class="bento" data-reveal-stagger>
                {{-- Step 1: Tap — draggable card, spring release --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.demo.tag') }}</span>
                    <h3>{{ __('landing.steps.items.0.title') }}</h3>
                    <p>{{ __('landing.steps.items.0.body') }}</p>
                    <div class="card-demo" id="demo-tap" aria-label="{{ __('landing.demo.tap_aria') }}">
                        <div class="demo-tap-stage">
                            <span class="demo-hint">{{ __('landing.demo.tap_hint') }}</span>
                            <div class="demo-tap-card">
                                <span class="chip" aria-hidden="true"></span>
                                <span class="id">0441</span>
                            </div>
                            <div class="demo-tap-reader">
                                <span class="light" aria-hidden="true"></span>
                                <span class="name">GATE-A</span>
                            </div>
                            <div class="demo-tap-readout">0441 @ GATE-A &rarr; attendance.in</div>
                        </div>
                    </div>
                    <pre class="card-snippet">tap <b>0441</b> @ <b>GATE-A</b></pre>
                </article>

                {{-- Step 2: Identify — ids converge into one event --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.demo.tag') }}</span>
                    <h3>{{ __('landing.steps.items.1.title') }}</h3>
                    <p>{{ __('landing.steps.items.1.body') }}</p>
                    <div class="card-demo" id="demo-identify" aria-label="{{ __('landing.demo.identify_aria') }}">
                        <div class="demo-id-pair" aria-hidden="true">
                            <span class="demo-id-chip id-card"><i></i>0441</span>
                            <span class="demo-id-chip id-reader"><i></i>GATE-A</span>
                            <span class="demo-id-join">who + where = <b>one event</b></span>
                        </div>
                    </div>
                    <pre class="card-snippet">{ card: <b>0441</b>, reader: <b>GATE-A</b> }</pre>
                </article>

                {{-- Step 3: Timestamp — the record is stamped, immutable --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.demo.tag') }}</span>
                    <h3>{{ __('landing.steps.items.2.title') }}</h3>
                    <p>{{ __('landing.steps.items.2.body') }}</p>
                    <div class="card-demo" id="demo-timestamp" aria-label="{{ __('landing.demo.stamp_aria') }}">
                        <div class="demo-stamp-stage" aria-hidden="true">
                            <span class="demo-stamp-ring"></span>
                            <div class="demo-stamp-record">
                                <b>07:58:12</b>
                                <span>{{ __('landing.demo.stamp_note') }}</span>
                            </div>
                        </div>
                    </div>
                    <pre class="card-snippet">07:58:12 <span class="s">&rarr;</span> <b>recorded</b></pre>
                </article>

                {{-- Step 4: Report — tallies build themselves --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.demo.tag') }}</span>
                    <h3>{{ __('landing.steps.items.3.title') }}</h3>
                    <p>{{ __('landing.steps.items.3.body') }}</p>
                    <div class="card-demo" id="demo-report" aria-label="{{ __('landing.demo.report_aria') }}">
                        <div class="demo-report-stage">
                            <div class="demo-report-row">
                                <span>{{ __('landing.demo.report_arrivals') }}</span>
                                <span class="demo-report-bar"><i></i></span>
                                <span class="num" data-value="187" data-pct="78">0</span>
                            </div>
                            <div class="demo-report-row is-scarlet">
                                <span>{{ __('landing.demo.report_meals') }}</span>
                                <span class="demo-report-bar"><i></i></span>
                                <span class="num" data-value="96" data-pct="46">0</span>
                            </div>
                            <div class="demo-report-row is-orange">
                                <span>{{ __('landing.demo.report_drops') }}</span>
                                <span class="demo-report-bar"><i></i></span>
                                <span class="num" data-value="42" data-pct="24">0</span>
                            </div>
                        </div>
                    </div>
                    <pre class="card-snippet">sum(events) <span class="s">&rarr;</span> <b>attendance.day</b></pre>
                </article>
            </div>
        </div>
    </section>

    {{-- ============ Applications — bento + pipeline ============ --}}
    <section class="section">
        <div class="shell">
            <div class="section-head-wide" data-reveal>
                <h2>{{ __('landing.apps.title') }}</h2>
                <p>{{ __('landing.apps.intro') }}</p>
            </div>

            <div class="bento" data-reveal-stagger>
                {{-- App 1: attendance.in — staggered check-in wave --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.apps.items.0.label') }}</span>
                    <h3>{{ __('landing.apps.items.0.title') }}</h3>
                    <p>{{ __('landing.apps.items.0.body') }}</p>
                    <div class="card-demo" id="demo-attendance" aria-label="{{ __('landing.demo.grid_aria') }}">
                        <div class="demo-grid-stage" aria-hidden="true">
                            <div class="demo-grid">
                                @foreach (range(1, 24) as $dot)
                                    <i></i>
                                @endforeach
                            </div>
                            <div class="demo-grid-legend">
                                <span>GATE-A &middot; 08:00</span>
                                <span>{{ __('landing.demo.grid_legend') }}</span>
                            </div>
                        </div>
                    </div>
                    <pre class="card-snippet">label: <b>attendance.in</b></pre>
                </article>

                {{-- App 2: meal.lunch — week tally grows --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.apps.items.1.label') }}</span>
                    <h3>{{ __('landing.apps.items.1.title') }}</h3>
                    <p>{{ __('landing.apps.items.1.body') }}</p>
                    <div class="card-demo" id="demo-meals" aria-label="{{ __('landing.demo.meals_aria') }}">
                        <div class="demo-meals-stage" aria-hidden="true">
                            @foreach ([0, 1, 2, 3, 4] as $day)
                                <div class="demo-meals-day">
                                    <span>{{ __('landing.demo.meals_days')[$day] }}</span>
                                    <span class="demo-meals-bar"><i></i></span>
                                    <span class="num" data-value="{{ [92, 88, 95, 87, 96][$day] }}" data-pct="{{ [82, 78, 85, 77, 86][$day] }}">0</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <pre class="card-snippet">label: <b>meal.lunch</b></pre>
                </article>

                {{-- App 3: recycle.drop — randomized scatter --}}
                <article class="card">
                    <span class="card-tag">{{ __('landing.apps.items.2.label') }}</span>
                    <h3>{{ __('landing.apps.items.2.title') }}</h3>
                    <p>{{ __('landing.apps.items.2.body') }}</p>
                    <div class="card-demo" id="demo-recycle" aria-label="{{ __('landing.demo.recycle_aria') }}">
                        <div class="demo-recycle-stage" aria-hidden="true">
                            <span class="demo-scrap s-teal"><i></i></span>
                            <span class="demo-scrap s-orange"><i></i></span>
                            <span class="demo-scrap s-scarlet"><i></i></span>
                            <span class="demo-scrap s-grey"><i></i></span>
                            <span class="demo-scrap s-teal"><i></i></span>
                            <span class="demo-scrap s-orange"><i></i></span>
                            <div class="demo-recycle-bin">
                                <span class="demo-recycle-count">+0</span>
                            </div>
                        </div>
                    </div>
                    <pre class="card-snippet">label: <b>recycle.drop</b></pre>
                </article>

                {{-- One event stream: SVG line-draw + motion-path tap --}}
                <article class="card pipeline-card">
                    <h3>{{ __('landing.demo.pipeline_title') }}</h3>
                    <p>{{ __('landing.apps.intro') }}</p>
                    <div class="card-demo" id="demo-pipeline" aria-hidden="true">
                        <div class="pipeline-stage">
                            <svg class="pipeline-svg" viewBox="0 0 760 190" fill="none" role="presentation">
                                <!-- stream paths (drawn on scroll) -->
                                <path class="p-draw p-main p-line-accent" d="M 138 95 L 320 95" />
                                <path class="p-draw p-line-accent" d="M 320 95 C 400 95 440 38 556 38" />
                                <path class="p-draw p-line-accent" d="M 320 95 L 556 95" />
                                <path class="p-draw p-line-accent" d="M 320 95 C 400 95 440 152 556 152" />
                                <!-- tap node -->
                                <g class="pipeline-node">
                                    <rect x="26" y="57" width="112" height="76" rx="8" />
                                    <rect class="pipeline-tap" x="42" y="70" width="36" height="24" rx="3" />
                                    <circle class="pipeline-dot" cx="58" cy="82" r="0" />
                                    <text x="42" y="112">tap 0441</text>
                                </g>
                                <!-- application nodes -->
                                <g class="pipeline-node">
                                    <rect x="556" y="20" width="178" height="36" rx="8" />
                                    <circle class="node-dot" cx="576" cy="38" r="4" />
                                    <text x="590" y="42">attendance.in</text>
                                </g>
                                <g class="pipeline-node">
                                    <rect x="556" y="77" width="178" height="36" rx="8" />
                                    <circle class="node-dot" cx="576" cy="95" r="4" />
                                    <text x="590" y="99">meal.lunch</text>
                                </g>
                                <g class="pipeline-node">
                                    <rect x="556" y="134" width="178" height="36" rx="8" />
                                    <circle class="node-dot" cx="576" cy="152" r="4" />
                                    <text x="590" y="156">recycle.drop</text>
                                </g>
                                <!-- the travelling tap -->
                                <circle class="pipeline-dot" cx="138" cy="95" r="5" />
                            </svg>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- ============ Package breakdown widget ============ --}}
    <section class="section section-wash">
        <div class="shell">
            <div class="section-head-wide" data-reveal>
                <h2>{{ __('landing.widget.title') }}</h2>
            </div>
            <div class="bento" data-reveal-stagger>
                <div class="widget" id="package-widget" aria-label="{{ __('landing.widget.title') }}">
                    <div class="widget-tabs" role="tablist">
                        @foreach (['starter', 'campus', 'enterprise'] as $tierKey)
                            <button type="button" class="widget-tab" role="tab"
                                    data-tier="{{ $tierKey }}"
                                    aria-selected="{{ $tierKey === 'starter' ? 'true' : 'false' }}">{{ ucfirst($tierKey) }}</button>
                        @endforeach
                    </div>
                    <div class="widget-body">
                        <div class="widget-rows">
                            <div class="widget-row is-accent" data-row="readers">
                                <span class="widget-row-label">{{ __('landing.widget.readers') }}</span>
                                <span class="widget-row-track"><span class="widget-row-fill"></span></span>
                                <span class="widget-row-value"><b>1</b></span>
                            </div>
                            <div class="widget-row" data-row="cards">
                                <span class="widget-row-label">{{ __('landing.widget.cards') }}</span>
                                <span class="widget-row-track"><span class="widget-row-fill"></span></span>
                                <span class="widget-row-value"><b>200</b></span>
                            </div>
                            <div class="widget-row is-spare" data-row="apps">
                                <span class="widget-row-label">{{ __('landing.widget.apps') }}</span>
                                <span class="widget-row-track"><span class="widget-row-fill"></span></span>
                                <span class="widget-row-value"><b>1</b></span>
                            </div>
                        </div>
                        <div class="widget-foot">
                            <p class="widget-note">{{ __('landing.widget.note') }}</p>
                            <a class="btn btn-quiet btn-topbar" href="{{ route('pricing') }}">{{ __('landing.widget.cta') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Audience ============ --}}
    <section class="section">
        <div class="shell section-grid">
            <div class="section-head" data-reveal>
                <h2>{{ __('landing.audience.title') }}</h2>
            </div>
            <div class="section-body">
                @foreach (__('landing.audience.items') as $item)
                    <div class="audience-row" data-reveal>
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
            <div data-reveal>
                <h2>{{ __('landing.closing.title') }}</h2>
                <p>{{ __('landing.closing.body') }}</p>
            </div>
            <div class="closing-actions" data-reveal>
                <a class="btn btn-primary" href="{{ route('product') }}">{{ __('landing.closing.cta_primary') }}</a>
                <a class="btn btn-quiet btn-quiet-invert" href="{{ route('contact.show') }}">{{ __('landing.closing.cta_secondary') }}</a>
            </div>
        </div>
    </section>
@endsection
