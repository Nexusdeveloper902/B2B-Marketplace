<?php

return [
    'meta_title' => 'Presence Platform — every card tap becomes a record',
    'meta_description' => 'Presence-event infrastructure for schools and organizations. One NFC card, one tap, one timestamped event: attendance, meal tracking, and recycling incentives.',

    'hero' => [
        'headline' => 'Every card tap becomes a record you can trust.',
        'pitch' => 'Presence Platform is presence-event infrastructure: one NFC card, one tap, one timestamped event. Attendance, meal service, and recycling incentives all read from the same stream.',
        'cta_primary' => 'See it in action',
        'cta_secondary' => 'Request a demo',
    ],

    'ledger' => [
        'title' => 'Event log — Riverside School',
        'live' => 'recording',
        'aria' => 'Illustrative event log: card taps recorded with time, card, reader, and event label',
        'columns' => ['time', 'card', 'reader', 'event'],
        'card_label' => 'CARD',
        'tap_aria' => 'Illustration of a card tapping a reader',
    ],

    'problem' => [
        'title' => 'The record is the problem.',
        'body_1' => 'Schools and organizations count the things that matter: who arrived, who ate, who participated, what came back to be recycled. Almost everywhere, that counting is still done by hand — attendance rolls, clickers, spreadsheets, and memory.',
        'body_2' => 'Hand counts drift. Totals get challenged at audit time. Assembling a month of reports takes days nobody has. And when the numbers decide funding, compliance, or staffing, "probably" is not good enough.',
        'costs_title' => 'What weak records cost',
        'costs' => [
            'Attendance disputes you cannot settle with evidence',
            'Meal-program counts that do not reconcile with eligibility lists',
            'Incentive programs that stall because nobody can tally them',
        ],
    ],

    'steps' => [
        'title' => 'From tap to report',
        'intro' => 'The platform records the same four-part event every time, no matter which application reads it.',
        'items' => [
            [
                'title' => 'Tap',
                'body' => 'A student or staff member presents their NFC card to a wall-mounted reader at the door, the service point, or the drop-off station. No app, no battery, nothing to pair.',
            ],
            [
                'title' => 'Identify',
                'body' => 'The reader reads the card\'s unique ID and pairs it with its own reader ID, so every event knows who and where.',
            ],
            [
                'title' => 'Timestamp',
                'body' => 'The platform stamps the event when it arrives and stores it as an immutable record — not a spreadsheet cell someone can overwrite.',
            ],
            [
                'title' => 'Report',
                'body' => 'Dashboards and reports read the same event stream. Attendance, meals, and incentives are tallies over the same records, not separate systems.',
            ],
        ],
    ],

    'apps' => [
        'title' => 'Three applications, one event stream',
        'intro' => 'Every package runs the same architecture. The applications are different reports over the same records.',
        'items' => [
            [
                'label' => 'attendance.in',
                'title' => 'Attendance',
                'body' => 'Entry and exit per person per day, at every reader you install. Daily totals build themselves; late arrivals and gaps are flagged instead of hunted.',
            ],
            [
                'label' => 'meal.lunch',
                'title' => 'Meal tracking (PAE)',
                'body' => 'Each subsidized meal is tied to the eligible student who tapped for it. Counts reconcile with your program lists by construction, and days end with a tally instead of an estimate.',
            ],
            [
                'label' => 'recycle.drop',
                'title' => 'Recycling incentives',
                'body' => 'Drop-offs at recycling points are credited to the person or group that tapped. Incentive tallies run themselves, and the evidence for every point is one lookup away.',
            ],
        ],
    ],

    'audience' => [
        'title' => 'Who it is for',
        'items' => [
            [
                'title' => 'Small schools',
                'body' => 'One reader at the main entrance and up to 200 cards. Attendance you can defend in any meeting.',
                'link' => 'See the Starter package',
                'href' => 'pricing',
            ],
            [
                'title' => 'Larger schools and campuses',
                'body' => 'Readers across gates, service points, and recycling stations, with meal tracking and incentives included.',
                'link' => 'See the Campus package',
                'href' => 'pricing',
            ],
            [
                'title' => 'Businesses and organizations',
                'body' => 'Custom event types for whatever you need to count: shifts, zones, assets, visits.',
                'link' => 'See Enterprise',
                'href' => 'enterprise',
            ],
        ],
    ],

    'closing' => [
        'title' => 'See what a tap can do.',
        'body' => 'Walk through the product in two minutes, or tell us what you need to count and we will show you the events.',
        'cta_primary' => 'See it in action',
        'cta_secondary' => 'Request a demo',
    ],
];
