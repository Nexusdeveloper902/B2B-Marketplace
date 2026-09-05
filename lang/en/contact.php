<?php

return [
    'meta_title' => 'Request a demo',

    'hero' => [
        'title' => 'Request a demo.',
        'body' => 'Tell us about your organization and what you need to count. We reply within two business days to set up a walkthrough.',
    ],

    'form' => [
        'name' => 'Full name',
        'email' => 'Work email',
        'organization' => 'Organization',
        'tier' => 'Package of interest',
        'tier_placeholder' => 'Choose a package',
        'tier_options' => [
            'starter' => 'Starter — small schools',
            'campus' => 'Campus — larger schools',
            'enterprise' => 'Enterprise — custom tracking',
            'unsure' => 'Not sure yet',
        ],
        'message' => 'What would you like to track?',
        'message_hint' => 'A sentence or two is enough: attendance, meals, recycling, or something custom.',
        'submit' => 'Send request',
        'privacy' => 'Your details are only used to answer this request.',
        'aria_errors' => 'The form has errors',
    ],

    'next' => [
        'title' => 'What happens next',
        'steps' => [
            [
                'title' => 'We reply within two business days',
                'body' => 'A short email confirming we received your request, with proposed times.',
            ],
            [
                'title' => 'A 30-minute walkthrough',
                'body' => 'We demo the platform against your use case — your doors, your counts, your labels.',
            ],
            [
                'title' => 'A pilot plan and a quote',
                'body' => 'A concrete rollout proposal: readers, cards, reports, and price.',
            ],
        ],
    ],

    'thankyou' => [
        'title' => 'Request received.',
        'body' => 'We have logged your request and will reply to :email within two business days.',
        'home' => 'Back to the homepage',
        'another' => 'Send another request',
    ],
];
