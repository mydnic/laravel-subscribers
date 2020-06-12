<?php
return [
    'verify' => env('LARAVEL_SUBSCRIBERS_VERIFY', false),
    'redirect_url' => 'home',
    /*
     |--------------------------------------------------------------------------
     | Notifications Mail Messages
     |--------------------------------------------------------------------------
     |
     */
    'mail' => [
        'verify' => [
            'subject' => 'Verify Email Address',
            'greeting' => 'Hello!',
            'content' => [
                'Please click the button below to verify your email address.'
            ],
            'action' => 'Verify Email Address',
            'footer' => [
                'If you did not sign up for our newsletter, no further action is required.'
            ],
        ]
    ]
];
