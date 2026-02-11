<?php

return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'bypass_payment' => env('BYPASS_PAYMENT', false),

    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@scandereai.store'),
        'notification_email' => env('ADMIN_NOTIFICATION_EMAIL'),
    ],
];
