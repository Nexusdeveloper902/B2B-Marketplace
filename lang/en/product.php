<?php

return [
    'meta_title' => 'Product — how a tap becomes a record',
    'meta_description' => 'One card, one tap, one timestamped event: the Presence Platform pipeline from NFC card and reader to the event platform and dashboards.',

    'hero' => [
        'headline' => 'One card. One tap. One timestamped event.',
        'body' => 'The core of Presence Platform is a single, small data shape: who tapped, where, at exactly what time, and what the tap counts as. Everything the platform offers — attendance sheets, meal tallies, incentive programs, dashboards — is built on that one record.',
    ],

    'pipeline' => [
        'title' => 'From card to dashboard',
        'intro' => 'The event makes the same trip every time, through four stations.',
        'blocks' => [
            [
                'label' => 'CARD',
                'title' => 'NFC card',
                'body' => 'A plain NFC card or keyfob with a unique ID. No battery, no app on anyone\'s phone, nothing to charge or pair.',
            ],
            [
                'label' => 'READER',
                'title' => 'Wall reader',
                'body' => 'A reader at the door or service point reads the card and pairs it with its own reader ID, so every event knows where it happened.',
            ],
            [
                'label' => 'PLATFORM',
                'title' => 'Event platform',
                'body' => 'The backend stamps the event with a server-side timestamp and stores it as an immutable record — the single source of truth.',
            ],
            [
                'label' => 'DASHBOARDS',
                'title' => 'Reports and dashboards',
                'body' => 'Attendance, meal, and incentive reports are computed views over the same event stream — never a second copy of the data.',
            ],
        ],
    ],

    'anatomy' => [
        'title' => 'Anatomy of an event',
        'intro' => 'Four fields. That is the whole idea.',
        'fields' => [
            [
                'key' => 'card',
                'title' => 'Who',
                'body' => 'The card ID maps to one student, staff member, or visitor. People are identified by what they carry, not by what they type.',
            ],
            [
                'key' => 'reader',
                'title' => 'Where',
                'body' => 'The reader ID maps to a known point: a gate, a hall, a meal service point, a drop-off station.',
            ],
            [
                'key' => 'at',
                'title' => 'When',
                'body' => 'A server-side timestamp, assigned when the event is ingested and never editable afterwards. The record, not the device, owns the time.',
            ],
            [
                'key' => 'type',
                'title' => 'What',
                'body' => 'The event label: attendance.in, meal.lunch, recycle.drop — or a custom label your organization defines.',
            ],
        ],
        'sample_title' => 'One event, as stored',
    ],

    'apps' => [
        'title' => 'Built on the same stream',
        'intro' => 'The three standard applications are reports over the same event records. Nothing is duplicated, so nothing can drift out of agreement.',
        'items' => [
            [
                'label' => 'attendance.in',
                'title' => 'Attendance',
                'body' => 'Arrival and exit per person per day. Reports build themselves from the stream: daily presence, late arrivals, gaps to follow up. A month of records is a query, not a folder of paper.',
            ],
            [
                'label' => 'meal.lunch',
                'title' => 'Meal tracking (PAE)',
                'body' => 'Every meal served is one tap by one eligible person. The day\'s tally is exact by construction, reconciles against eligibility lists, and the evidence for any disputed meal is a single lookup.',
            ],
            [
                'label' => 'recycle.drop',
                'title' => 'Recycling incentives',
                'body' => 'Drop-offs tap a card at the station and are credited instantly. Points, prizes, and standings are running totals over labeled events — the program runs itself while the evidence accumulates.',
            ],
        ],
    ],

    'note' => [
        'title' => 'Adding a fourth application is a label, not a rebuild',
        'body' => 'Because applications are views over labeled events, a new use case means defining a new event label and its report — not standing up a separate system. That is the same move the Enterprise package makes for custom tracking.',
        'link' => 'How custom tracking works',
    ],
];
