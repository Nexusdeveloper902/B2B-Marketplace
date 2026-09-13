/**
 * Pulse — Marketplace Storefront
 * Motion layer (TASK-012, 2026-09-06). anime.js v4.5.0, self-hosted ESM.
 *
 * Architecture:
 *  - One root createScope(document.body) with mediaQueries for responsive
 *    animation variants; constructor returns a cleanup fn (scope.revert()).
 *  - Page behaviour keyed off body[data-page]; demos exist on the landing
 *    page only, reveals work wherever [data-reveal] appears.
 *  - prefers-reduced-motion: the inline head script never adds .js-motion,
 *    so every [data-reveal]/hero element stays visible and this module
 *    exits before creating any animation. Static CSS states apply.
 *  - CSS owns the hidden initial states; anime.js owns the tweens; onScroll
 *    (Scroll Observer) owns the enter triggers. No hand-rolled IO.
 */
import {
    animate,
    createTimeline,
    stagger,
    createScope,
    onScroll,
    createDraggable,
    spring,
    createMotionPath,
    createDrawable,
    utils,
} from './vendor/anime.esm.min.js';

const doc = document.documentElement;
const body = document.body;
const isLanding = body.dataset.page === 'landing';
const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* Token values resolved from the CSS custom properties (no duplicated hex). */
const cssVar = (name) => getComputedStyle(doc).getPropertyValue(name).trim();
const T = {
    data: cssVar('--data'),            // muted-teal 400
    dataSolid: cssVar('--data-solid'), // muted-teal 500
    dataTint: cssVar('--data-tint'),   // muted-teal 900
    surface: cssVar('--surface'),      // shadow-grey 800
    borderStrong: cssVar('--border-strong'),
    accent: cssVar('--accent-soft'),   // scarlet 400
};

const $ = (sel, ctx) => (ctx || document).querySelector(sel);
const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

/* Resize-driven loop rebuild registry (distances depend on layout). */
const loops = [];
const registerLoop = (destroy) => loops.push(destroy);
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => loops.forEach((destroy) => destroy()), 250);
});

/* ------------------------------------------------------------------ */
/*  Generic scroll reveals — onScroll drives class hand-off to CSS.   */
/* ------------------------------------------------------------------ */
function setupReveals() {
    $$('[data-reveal]').forEach((el) => {
        onScroll({
            target: el,
            enter: '85% center',
            repeat: false,
            onEnter: () => el.classList.add('is-revealed'),
        });
    });

    /* Staggered groups: cards animate in sequence via anime.js stagger. */
    $$('[data-reveal-stagger]').forEach((group) => {
        const children = Array.from(group.children);
        onScroll({
            target: group,
            enter: '85% center',
            repeat: false,
            onEnter: () => {
                group.classList.add('is-revealed');
                animate(children, {
                    opacity: [0, 1],
                    translateY: [22, 0],
                    duration: 650,
                    ease: 'out(3)',
                    delay: stagger(90),
                });
            },
        });
    });
}

/* ------------------------------------------------------------------ */
/*  Hero entrance (landing, above the fold)                           */
/* ------------------------------------------------------------------ */
function heroEntrance() {
    const els = [
        $('.hero-kicker'),
        $('.hero-copy h1'),
        $('.hero-pitch'),
        $('.hero-chip'),
        $('.hero-actions'),
        $('.ledger'),
    ].filter(Boolean);
    animate(els, {
        opacity: [0, 1],
        translateY: [26, 0],
        duration: 850,
        ease: 'out(3)',
        delay: stagger(110),
        onComplete: () => els.forEach((el) => el.classList.add('is-revealed')),
    });

    /* Copy the sample event on click (install-chip behaviour). */
    const chip = $('.hero-chip');
    if (chip) {
        const copyChip = async () => {
            try {
                await navigator.clipboard.writeText(chip.dataset.copy || '');
                const prompt = $('.chip-prompt', chip);
                const original = prompt.textContent;
                prompt.textContent = prompt.dataset.copied || 'copied';
                setTimeout(() => { prompt.textContent = original; }, 1200);
            } catch (e) { /* clipboard unavailable — no-op */ }
        };
        chip.addEventListener('click', copyChip);
        // role="button" must come with button semantics: Enter and Space
        // activate it (WCAG 2.1.1 — a div does not synthesize clicks).
        chip.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                copyChip();
            }
        });
    }
}

/* ------------------------------------------------------------------ */
/*  Hero ledger: card taps reader -> go-light -> row written          */
/*  (createTimeline, loop, chained callbacks per row cycle)           */
/* ------------------------------------------------------------------ */
function ledgerLoop() {
    const stage = $('.tap-visual');
    const card = $('.tap-card', stage);
    const target = $('.tap-target', stage);
    const light = $('.tap-light', stage);
    const wave = $$('.tap-wave i', stage);
    const rows = $$('.ledger-table tbody tr');
    if (!stage || !card || !target || rows.length === 0) return;

    const distance = () => {
        // The real layout gap between card and reader (offsetLeft is
        // transform-free, so a mid-flight animation can't poison the
        // measure) minus a 14px stop-short margin. With the centered
        // pair this equals the flex gap; it also stays correct if the
        // layout changes again.
        const gap = target.offsetLeft - (card.offsetLeft + card.offsetWidth);
        return Math.max(40, gap - 14);
    };

    /* Row cycling is a chained callback: a 1ms object tween inside the
       timeline fires flashRow at the exact "tap" moment, each loop. */
    let rowIndex = 0;
    const flashRow = () => {
        const tds = rows[rowIndex % rows.length].children;
        rows.forEach((r) => r.classList.remove('is-new'));
        rows[rowIndex % rows.length].classList.add('is-new');
        rowIndex += 1;
        animate(tds, {
            backgroundColor: [
                'rgba(128, 179, 167, 0.16)',
                'rgba(128, 179, 167, 0)',
            ],
            duration: 950,
            ease: 'out(2)',
        });
    };
    const flashProxy = { written: 0 };

    let tl;
    const build = () => {
        if (tl) { tl.pause(); }
        tl = createTimeline({
            defaults: { ease: 'inOut(2)' },
            loop: true,
            loopDelay: 350,
        });
        tl.add(card, { translateX: () => distance(), duration: 950 }, 0)
          .add(light, {
              backgroundColor: [T.borderStrong, T.data],
              scale: [1, 1.45],
              duration: 170,
              ease: 'out(2)',
          }, 1000)
          .add(wave, {
              scaleY: [1, 1.8, 1],
              duration: 380,
              ease: 'inOut(2)',
              delay: stagger(55),
          }, 1000)
          .add(light, {
              backgroundColor: [T.data, T.borderStrong],
              scale: [1.45, 1],
              duration: 420,
              ease: 'out(2)',
          }, 1290)
          .add(flashProxy, {
              written: 1,
              duration: 1,
              onComplete: flashRow,
          }, 1180)
          .add(card, { translateX: 0, duration: 950 }, 2150);
    };
    build();
    registerLoop(() => build());
}

/* ------------------------------------------------------------------ */
/*  Bento demo 1 — Tap: draggable card, spring release physics        */
/* ------------------------------------------------------------------ */
function demoTap() {
    const stage = $('#demo-tap');
    if (!stage) return;
    const cardEl = $('.demo-tap-card', stage);
    const readerEl = $('.demo-tap-reader', stage);
    const readout = $('.demo-tap-readout', stage);
    const lightEl = $('.light', readerEl);

    const clearHit = () => {
        readerEl.classList.remove('is-hit');
        readout.classList.remove('is-on');
    };

    createDraggable(cardEl, {
        container: stage,
        x: true,
        y: false,
        releaseStiffness: 80,   /* spring back — createDraggable spring release */
        releaseDamping: 11,
        dragThreshold: { mouse: 3, touch: 8 },
        cursor: { onHover: 'grab', onGrab: 'grabbing' },
        onGrab: clearHit,
        onRelease: (self) => {
            const c = cardEl.getBoundingClientRect();
            const r = readerEl.getBoundingClientRect();
            const reached = c.left + c.width >= r.left - 14;
            if (reached) {
                readerEl.classList.add('is-hit');
                readout.classList.add('is-on');
                animate(lightEl, { scale: [1, 1.7, 1], duration: 560, ease: 'out(2)' });
            }
            void self;
        },
        onSettle: clearHit,
    });
}

/* ------------------------------------------------------------------ */
/*  Bento demo 2 — Identify: card id + reader id converge into event  */
/* ------------------------------------------------------------------ */
function demoIdentify() {
    const stage = $('#demo-identify');
    if (!stage) return;
    const chipCard = $('.id-card', stage);
    const chipReader = $('.id-reader', stage);
    const join = $('.demo-id-join', stage);
    if (!chipCard || !chipReader || !join) return;

    const stageEl = stage.querySelector('.demo-id-pair') || stage;
    const toCenter = (el, side) =>
        (stageEl.clientWidth / 2) - (el.offsetLeft + el.offsetWidth / 2) + side;

    let tl;
    const build = () => {
        if (tl) { tl.pause(); }
        tl = createTimeline({ defaults: { ease: 'inOut(2)' }, loop: true, loopDelay: 900 });
        tl.add([chipCard, chipReader], {
            translateX: (el, i) => toCenter(el, i === 0 ? -64 : 64),
            duration: 800,
        }, 0)
          .add([chipCard, chipReader], { opacity: [1, 0.25], duration: 200 }, 800)
          .add(join, {
              opacity: [0, 1],
              translateY: [34, 26],
              duration: 380,
              ease: spring({ stiffness: 120, damping: 13 }),
          }, 850)
          .add(join, { opacity: [1, 0], duration: 260 }, 2500)
          .add([chipCard, chipReader], {
              translateX: 0,
              opacity: 1,
              duration: 500,
          }, 2600);
    };
    build();
    registerLoop(() => build());
}

/* ------------------------------------------------------------------ */
/*  Bento demo 3 — Timestamp: immutable record, stamped each loop     */
/* ------------------------------------------------------------------ */
function demoTimestamp() {
    const stage = $('#demo-timestamp');
    if (!stage) return;
    const ring = $('.demo-stamp-ring', stage);
    const record = $('.demo-stamp-record', stage);
    const timeEl = $('b', record);
    if (!ring || !record || !timeEl) return;

    /* Cycle the illustrative times taken from the ledger data. */
    const times = ['07:58:12', '08:02:47', '12:14:03', '15:41:09', '16:22:41'];
    let i = 0;

    animate(ring, { rotate: 360, duration: 11000, ease: 'linear', loop: true });

    const tl = createTimeline({ loop: true, loopDelay: 500, defaults: { ease: 'out(3)' } });
    tl.add(record, { scale: [1, 1.14], duration: 150 })
      .add(record, {
          scale: 1,
          duration: 620,
          ease: spring({ stiffness: 170, damping: 13 }),
      }, 150)
      .add({ tick: 0 }, {
          tick: 1,
          duration: 1,
          onComplete: () => {
              i = (i + 1) % times.length;
              timeEl.textContent = times[i];
          },
      }, 800);
}

/* ------------------------------------------------------------------ */
/*  Bento demo 4 — Report: tallies build themselves on scroll         */
/* ------------------------------------------------------------------ */
function demoReport() {
    const stage = $('#demo-report');
    if (!stage) return;
    const rows = $$('.demo-report-row', stage);
    if (rows.length === 0) return;

    const run = () => {
        rows.forEach((row, idx) => {
            const bar = $('.demo-report-bar i', row);
            const num = $('.num', row);
            const target = parseInt(num.dataset.value || '0', 10);
            const pct = num.dataset.pct || '60';
            const counter = { v: 0 };
            animate(bar, { width: [`0%`, `${pct}%`], duration: 1100, delay: idx * 140, ease: 'out(3)' });
            animate(counter, {
                v: target,
                duration: 1100,
                delay: idx * 140,
                ease: 'out(3)',
                onUpdate: () => { num.textContent = Math.round(counter.v); },
            });
        });
    };

    onScroll({
        target: stage,
        enter: '85% center',
        repeat: false,
        onEnter: run,
    });
    stage.addEventListener('click', run);
}

/* ------------------------------------------------------------------ */
/*  Bento demo 5 — Attendance: staggered grid wave from center        */
/* ------------------------------------------------------------------ */
function demoAttendance() {
    const stage = $('#demo-attendance');
    if (!stage) return;
    const dots = $$('.demo-grid i', stage);
    if (dots.length === 0) return;

    animate(dots, {
        backgroundColor: [T.surface, T.dataSolid, T.surface],
        scale: [0.82, 1, 0.82],
        duration: 1500,
        ease: 'inOut(2)',
        delay: stagger(90, { grid: [3, 8], from: 'center' }),
        loop: true,
        alternate: true,
    });
}

/* ------------------------------------------------------------------ */
/*  Bento demo 6 — Meals: week tally grows on scroll                  */
/* ------------------------------------------------------------------ */
function demoMeals() {
    const stage = $('#demo-meals');
    if (!stage) return;
    const days = $$('.demo-meals-day', stage);
    if (days.length === 0) return;

    const run = () => {
        days.forEach((day, idx) => {
            const bar = $('i', day);
            const num = $('.num', day);
            const target = parseInt(num.dataset.value || '0', 10);
            const pct = num.dataset.pct || '60';
            const counter = { v: 0 };
            animate(bar, { width: ['0%', `${pct}%`], duration: 800, delay: idx * 110, ease: 'out(3)' });
            animate(counter, {
                v: target,
                duration: 800,
                delay: idx * 110,
                ease: 'out(3)',
                onUpdate: () => { num.textContent = Math.round(counter.v); },
            });
        });
    };

    onScroll({ target: stage, enter: '85% center', repeat: false, onEnter: run });
    stage.addEventListener('click', run);
}

/* ------------------------------------------------------------------ */
/*  Bento demo 7 — Recycling: randomized scatter drop, tallied        */
/* ------------------------------------------------------------------ */
function demoRecycle() {
    const stage = $('#demo-recycle');
    if (!stage) return;
    const scraps = $$('.demo-scrap', stage);
    const countEl = $('.demo-recycle-count', stage);
    if (scraps.length === 0 || !countEl) return;

    let drops = 0;
    const run = () => {
        scraps.forEach((scrap, idx) => {
            const startX = utils.random(8, 78);
            scrap.style.left = `${startX}%`;
            const drift = utils.random(-26, 26);
            const spin = utils.random(-160, 160);
            animate(scrap, {
                translateY: [0, 52],
                translateX: [0, drift],
                rotate: [0, spin],
                opacity: [0, 1, 1, 0],
                duration: 900,
                delay: idx * 130 + utils.random(0, 90),
                ease: 'in(2)',
                onComplete: () => {
                    drops += 1;
                    countEl.textContent = `+${drops}`;
                },
            });
        });
    };

    onScroll({
        target: stage,
        enter: '85% center',
        repeat: true,
        onEnter: run,
    });
}

/* ------------------------------------------------------------------ */
/*  Pipeline: SVG line-draw + motion-path dot (one event stream)      */
/* ------------------------------------------------------------------ */
function demoPipeline() {
    const stage = $('#demo-pipeline');
    if (!stage) return;
    const paths = $$('.p-draw', stage);
    const dot = $('.pipeline-dot', stage);
    const mainPath = $('.p-main', stage);
    if (paths.length === 0) return;

    /* Line-draw on scroll: createDrawable + draw '0 -> 1'. */
    const drawables = createDrawable(paths);
    animate(drawables, {
        draw: ['0 0', '0 1'],
        duration: 1400,
        delay: stagger(160),
        ease: 'inOut(2)',
        autoplay: onScroll({ target: stage, enter: '80% center', repeat: false }),
    });

    /* A tap travels the stream forever (motion path on the SVG path). */
    if (dot && mainPath) {
        const mp = createMotionPath(mainPath);
        animate(dot, {
            ...mp,
            duration: 2800,
            ease: 'inOut(1)',
            loop: true,
            loopDelay: 300,
        });
    }
}

/* ------------------------------------------------------------------ */
/*  Package breakdown widget — real numbers from the published tiers  */
/* ------------------------------------------------------------------ */
const PACKAGE_DATA = {
    starter: {
        readers: { text: '1', num: 1, pct: 12 },
        cards: { text: '200', num: 200, pct: 12 },
        apps: { text: '1', num: 1, pct: 34 },
    },
    campus: {
        readers: { text: '2\u201310', num: null, pct: 55 },
        cards: { text: '2,000', num: 2000, pct: 100 },
        apps: { text: '3', num: 3, pct: 100 },
    },
    enterprise: {
        readers: { text: '\u221e', num: null, pct: 100 },
        cards: { text: '\u221e', num: null, pct: 100 },
        apps: { text: '3+', num: null, pct: 100 },
    },
};

function setupWidget() {
    const widget = $('#package-widget');
    if (!widget) return;
    const tabs = $$('.widget-tab', widget);
    const fills = {
        readers: $('.widget-row[data-row="readers"] .widget-row-fill', widget),
        cards: $('.widget-row[data-row="cards"] .widget-row-fill', widget),
        apps: $('.widget-row[data-row="apps"] .widget-row-fill', widget),
    };
    const values = {
        readers: $('.widget-row[data-row="readers"] .widget-row-value b', widget),
        cards: $('.widget-row[data-row="cards"] .widget-row-value b', widget),
        apps: $('.widget-row[data-row="apps"] .widget-row-value b', widget),
    };
    const rows = $$('.widget-row', widget);

    const select = (key, animateIn = true) => {
        tabs.forEach((t) => t.setAttribute('aria-selected', String(t.dataset.tier === key)));
        const data = PACKAGE_DATA[key];
        ['readers', 'cards', 'apps'].forEach((rowKey, idx) => {
            const d = data[rowKey];
            if (animateIn) {
                animate(fills[rowKey], {
                    width: [fills[rowKey].style.width || '0%', `${d.pct}%`],
                    duration: 800,
                    delay: idx * 70,
                    ease: spring({ stiffness: 70, damping: 14 }),
                });
                const valEl = values[rowKey];
                animate(valEl, {
                    opacity: [1, 0],
                    translateY: [0, -6],
                    duration: 160,
                    ease: 'out(2)',
                    onComplete: () => {
                        valEl.textContent = d.num !== null ? '0' : d.text;
                        animate(valEl, {
                            opacity: [0, 1],
                            translateY: [6, 0],
                            duration: 240,
                            ease: 'out(2)',
                        });
                        if (d.num !== null) {
                            const counter = { v: 0 };
                            animate(counter, {
                                v: d.num,
                                duration: 850,
                                delay: idx * 60,
                                ease: 'out(3)',
                                onUpdate: () => {
                                    valEl.textContent = Math.round(counter.v).toLocaleString('en-US');
                                },
                                onComplete: () => { valEl.textContent = d.text; },
                            });
                        }
                    },
                });
            } else {
                fills[rowKey].style.width = `${d.pct}%`;
                values[rowKey].textContent = d.text;
            }
        });
        animate(rows, {
            translateY: [0, -3, 0],
            duration: 420,
            delay: stagger(50),
            ease: 'inOut(2)',
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => select(tab.dataset.tier));
    });

    /* Initial state without animation; animate on first scroll-in. */
    select('starter', false);
    onScroll({
        target: widget,
        enter: '85% center',
        repeat: false,
        onEnter: () => select('starter'),
    });
}

/* ------------------------------------------------------------------ */
/*  Boot                                                              */
/* ------------------------------------------------------------------ */
if (!prefersReduced) {
    const scope = createScope({
        root: body,
        mediaQueries: {
            compact: '(max-width: 940px)',
            mobile: '(max-width: 620px)',
        },
    }).add((self) => {
        void self;
        setupReveals();

        if (isLanding) {
            heroEntrance();
            ledgerLoop();
            demoTap();
            demoIdentify();
            demoTimestamp();
            demoReport();
            demoAttendance();
            demoMeals();
            demoRecycle();
            demoPipeline();
            setupWidget();
        }

        /* Cleanup contract for the scope (component teardown pattern). */
        return () => {
            loops.forEach((destroy) => destroy());
            loops.length = 0;
        };
    });
}
