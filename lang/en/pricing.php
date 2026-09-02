<?php

return [
    'meta_title' => 'Packages and pricing',
    'meta_description' => 'Starter, Campus, and Enterprise packages on the same presence-event platform. Flat setup plus per-card for small schools; volume pricing for campuses; custom quote for enterprises.',

    'hero' => [
        'title' => 'Three packages, one platform',
        'body' => 'Every package runs on the same presence-event architecture: card tap, identified event, timestamped record, report. The packages differ in scope — readers, cards, and applications — not in kind.',
    ],

    'tiers' => [
        [
            'name' => 'Starter',
            'audience' => 'for small schools',
            'price' => 'Flat setup + per card',
            'footnote' => 'A one-time setup fee that includes installation and staff training, plus a small per-card charge.',
            'features' => [
                '1 reader at your main entrance',
                'Up to 200 cards',
                'Attendance events: arrival and exit',
                'Daily and monthly attendance reports',
                'Late-arrival flags by default',
                'Installation and staff training included',
                'Email support, next-business-day response',
            ],
            'cta' => 'Request a quote',
        ],
        [
            'name' => 'Campus',
            'audience' => 'for larger schools and campuses',
            'price' => 'Setup + per reader + per card',
            'footnote' => 'Reader and card pricing applies per unit, with volume discounts as counts grow.',
            'features' => [
                'Everything in Starter',
                '2 to 10 readers: gates, halls, service points',
                'Up to 2,000 cards',
                'PAE meal tracking at meal service points',
                'Recycling incentive module at drop-off points',
                'Per-building and per-grade reports',
                'Priority support, same-day response',
            ],
            'cta' => 'Request a quote',
        ],
        [
            'name' => 'Enterprise',
            'audience' => 'for businesses and organizations',
            'price' => 'Custom quote',
            'footnote' => 'Priced after a scoping conversation: number of sites, readers, cards, and custom event types.',
            'features' => [
                'Core presence-event platform',
                'Unlimited readers and cards',
                'Custom event types for any use case',
                'API access to your event stream',
                'Dashboards, exports, and integrations',
                'SLA with priority support',
                'Rollout planning and pilot program',
            ],
            'cta' => 'Talk to us',
        ],
    ],

    'strip' => [
        'title' => 'The same architecture in every package',
        'body' => 'A Starter attendance report and an Enterprise custom dashboard read the same kind of record. Moving up a package adds readers, cards, and event types — it never means migrating to a different system.',
        'pipeline' => ['tap', 'event', 'record', 'report'],
    ],
];
