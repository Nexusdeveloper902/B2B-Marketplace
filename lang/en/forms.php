<?php

return [
    'validation' => [
        'name.required' => 'Enter your full name.',
        'name.string' => 'Enter your full name.',
        'name.min' => 'Your name needs at least 2 characters.',
        'name.max' => 'Your name is too long.',

        'email.required' => 'Enter your work email address.',
        'email.email' => 'That does not look like a valid email address.',
        'email.max' => 'Your email address is too long.',

        'organization.required' => 'Enter your organization\'s name.',
        'organization.string' => 'Enter your organization\'s name.',
        'organization.min' => 'Your organization\'s name needs at least 2 characters.',
        'organization.max' => 'Your organization\'s name is too long.',

        'tier.required' => 'Choose a package of interest.',
        'tier.string' => 'Choose a package of interest.',
        'tier.in' => 'Choose a package from the list.',

        'message.required' => 'Tell us what you would like to track.',
        'message.string' => 'Tell us what you would like to track.',
        'message.min' => 'A sentence or two is enough (10 characters minimum).',
        'message.max' => 'Please keep the message under 2,000 characters.',
    ],
];
