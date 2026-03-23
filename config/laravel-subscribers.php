<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Email Verification
     |--------------------------------------------------------------------------
     | When enabled, subscribers must verify their email before being considered active.
     */
    'verify' => env('LARAVEL_SUBSCRIBERS_VERIFY', false),

    /*
     |--------------------------------------------------------------------------
     | Redirect URL
     |--------------------------------------------------------------------------
     | Named route to redirect to after subscription (web form only).
     */
    'redirect_url' => 'home',

    /*
     |--------------------------------------------------------------------------
     | Verification Email Content
     |--------------------------------------------------------------------------
     */
    'mail' => [
        'verify' => [
            'expiration' => 60, // in minutes
            'subject' => 'Verify Email Address',
            'greeting' => 'Hello!',
            'content' => [
                'Please click the button below to verify your email address.',
            ],
            'action' => 'Verify Email Address',
            'footer' => [
                'If you did not sign up for our newsletter, no further action is required.',
            ],
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Campaigns
     |--------------------------------------------------------------------------
     | Configuration for the newsletter campaign feature.
     */
    'campaigns' => [
        'enabled' => true,

        // Middleware applied to campaign management API routes
        'middleware' => ['api'],

        // Default sender for campaigns (falls back to config('mail.from.*'))
        'from' => [
            'name' => env('MAIL_FROM_NAME', 'Newsletter'),
            'email' => env('MAIL_FROM_ADDRESS', 'newsletter@example.com'),
        ],

        // Queue name for campaign send jobs
        'queue' => env('SUBSCRIBERS_QUEUE', 'default'),

        // Automatically register the `subscribers:dispatch-scheduled` command on the scheduler
        // running every minute. Set to false if you prefer to schedule it yourself.
        'schedule' => true,
    ],

    /*
     |--------------------------------------------------------------------------
     | Tracking
     |--------------------------------------------------------------------------
     | Open tracking (pixel) and click tracking (link proxy).
     */
    'tracking' => [
        'enabled' => true,
        'open' => true,
        'click' => true,

        // Allowlist of domains for click tracking. Empty = allow all.
        // Example: ['mysite.com', 'blog.mysite.com']
        'allowed_domains' => [],
    ],
];
