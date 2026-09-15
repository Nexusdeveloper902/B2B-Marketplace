<?php

/*
| Storefront chrome (TASK-014 marketplace layer): navigation, catalog,
| quote-list and comparison LABELS only. Product facts are never defined
| here — every claim the catalog shows is read from landing/product/
| pricing/enterprise so the two can't drift.
*/

return [
    'breadcrumb' => [
        'aria' => 'Breadcrumb',
        'home' => 'Home',
    ],

    'categories' => [
        'title' => 'Browse the catalog',
        'packages' => 'Packages',
        'packages_meta' => ':count packages',
        'apps' => 'Applications',
        'apps_meta' => ':count applications',
        'cases' => 'Custom tracking',
        'cases_meta' => ':count use cases',
        'how' => 'How it works',
        'how_meta' => ':count stations',
    ],

    'featured' => 'Featured',
    'ticker_aria' => 'Event labels shown across this site',
    'see_all' => 'See all',
    'details' => 'Details',
    'included_in' => 'Included in',
    'prev' => 'Previous',
    'next' => 'Next',

    'quote' => [
        'button' => 'Quote list',
        'count_aria' => 'items in your quote list',
        'title' => 'Your quote list',
        'note' => 'What you add here is attached to your quote request. It is not a cart: nothing is bought on this site and prices are quoted by our team.',
        'empty' => 'Your quote list is empty. Add packages, applications, or use cases from the catalog.',
        'add' => 'Add to quote',
        'added' => 'In quote list',
        'remove' => 'Remove',
        'close' => 'Close',
        'clear' => 'Clear list',
        'cta' => 'Request a quote',
        'browse' => 'Browse packages',
        'message_prefix' => 'Quote list:',
        'summary_empty' => 'No items yet. Add some from the catalog, or just describe what you need in the form.',
        'groups' => [
            'pkg' => 'Package',
            'app' => 'Application',
            'case' => 'Use case',
        ],
    ],

    'compare' => [
        'title' => 'Compare packages',
        'intro' => 'Every line below is taken from the package descriptions above.',
        'feature' => 'Feature',
        'included' => 'Included',
        'not_listed' => 'Not listed',
        'rows' => [
            'price' => 'Pricing model',
            'readers' => 'Readers',
            'cards' => 'Cards',
            'attendance' => 'Attendance (attendance.in)',
            'meals' => 'Meal tracking (meal.lunch)',
            'recycling' => 'Recycling incentives (recycle.drop)',
            'custom' => 'Custom event types',
            'api' => 'API access',
            'reports' => 'Reports',
            'onboarding' => 'Onboarding',
            'support' => 'Support',
        ],
    ],

    'pdp' => [
        'gallery_aria' => 'Illustration: a card tap at GATE-A becomes one stored event',
        'choose' => 'Choose a package',
        'apps' => 'Applications',
        'fields' => 'Every event stores',
        'tabs_aria' => 'On this page',
    ],
];
