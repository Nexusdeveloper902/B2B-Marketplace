<?php

return [
    'meta_title' => 'Enterprise — custom event tracking',
    'meta_description' => 'The tap, identify, timestamp, label pattern applied to any use case: shift check-in, zone access, asset checkout, visitor logging. Custom event types with API access.',

    'hero' => [
        'title' => 'If a moment of presence can be named, the platform can count it.',
        'body' => 'The Enterprise package gives you the full presence-event platform with custom event labels. The pattern never changes: tap, identify, timestamp, label. Your organization decides what the labels mean.',
    ],

    'pattern' => [
        'title' => 'The pattern, once more',
        'intro' => 'Every custom use case is the same four-field event, with a label you define.',
        'fields' => [
            [
                'key' => 'card',
                'title' => 'Who',
                'body' => 'One card, one person — staff, contractor, or visitor. What they carry is their identity at every reader.',
            ],
            [
                'key' => 'reader',
                'title' => 'Where',
                'body' => 'One reader, one known point in your operation: dock, lab, toolshed, reception.',
            ],
            [
                'key' => 'at',
                'title' => 'When',
                'body' => 'A server timestamp fixed at ingestion. When the record says 06:59:58, that is the time that counts.',
            ],
            [
                'key' => 'type',
                'title' => 'What',
                'body' => 'The label you define: shift.begin, zone.enter, asset.out, visitor.in — or anything your operation names.',
            ],
        ],
    ],

    'cases' => [
        'title' => 'What it looks like in practice',
        'intro' => 'Four examples of the same event shape, doing four different jobs.',
        'items' => [
            [
                'time' => '06:59:58',
                'card' => '1188',
                'reader' => 'DOCK-1',
                'type' => 'shift.begin',
                'title' => 'Shift check-in',
                'body' => 'Clock-in at the dock or workshop door, with the same evidence quality as school attendance: server timestamp, person, place. Payroll disputes end at the record.',
            ],
            [
                'time' => '11:40:12',
                'card' => '0219',
                'reader' => 'LAB-B',
                'type' => 'zone.enter',
                'title' => 'Zone access',
                'body' => 'Staff tap into restricted zones — labs, server rooms, warehouses. Access logs build themselves, and "who was in the building" is a query, not an afternoon of badge-system exports.',
            ],
            [
                'time' => '14:02:31',
                'card' => '0441',
                'reader' => 'TOOLSHED',
                'type' => 'asset.out',
                'title' => 'Asset checkout',
                'body' => 'Tools and equipment are checked out by tapping the borrower\'s card at the cage or shelf. Who has the tool, since when, and where it left from — one record each.',
            ],
            [
                'time' => '09:15:00',
                'card' => '9001',
                'reader' => 'RECEPTION',
                'type' => 'visitor.in',
                'title' => 'Visitor logging',
                'body' => 'Visitors receive a card at reception; their entries and exits are logged like everyone else\'s. The visitor log is a report, not a clipboard.',
            ],
        ],
    ],

    'includes' => [
        'title' => 'What the Enterprise package includes',
        'items' => [
            'Custom event types — any label your operation needs',
            'API access to your full event stream',
            'Dashboards, exports, and integrations',
            'Unlimited readers and cards across sites',
            'Rollout planning, pilot program, and staff training',
            'SLA with priority support',
        ],
        'link' => 'Discuss your use case',
    ],
];
