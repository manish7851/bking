<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'token' => env('SLACK_TOKEN'),
    ],
    
    'tracking' => [
        'api_key' => env('TRACKING_API_KEY', 'BUS-TRACKING-API-KEY-2025'),
    ],

    'khalti' => [
        'public_key' => env('KHALTI_PUBLIC_KEY', 'test_public_key'),
        'secret_key' => env('KHALTI_SECRET_KEY', 'test_secret_key'),
        'test_mode' => env('KHALTI_TEST_MODE', true),
    ],

    'esewa' => [
        'merchant_id' => env('ESEWA_MERCHANT_ID', 'EPAYTEST'),
        'url' => env('ESEWA_URL', 'https://rc.esewa.com.np/epay/main'),
        'verification_url' => env('ESEWA_VERIFICATION_URL', 'https://rc.esewa.com.np/epay/transrec'),
    ],

];
