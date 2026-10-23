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
    'fast2sms' => [
        'api_key' => env('FAST2SMS_API_KEY'),
        'sender_id' => env('FAST2SMS_SENDER_ID'),
        'template_id' => env('FAST2SMS_TEMPLATE_ID'),
        'url' => env('FAST2SMS_URL'),
        'route' => env('FAST2SMS_ROUTE'),
    ],
    'license' => [
        'url'          => env('LICENSE_API_URL'),
        'dev_hosts'    => array_filter(array_map('trim', explode(',', env('LICENSE_DEV_HOSTS', 'localhost,127.0.0.1,127.0.1.1,::1')))),
        'dev_prefixes' => array_filter(array_map('trim', explode(',', env('LICENSE_DEV_PREFIXES', 'dev')))),
    ],
    'geojs' => [
        'base_url' => env('GEOJS_BASE_URL', 'https://get.geojs.io'),
        'timeout_seconds' => env('GEOJS_TIMEOUT_SECONDS', 5),
        'cache_ttl_minutes' => env('GEOJS_CACHE_TTL_MINUTES', 1440),
    ],
    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
        'currency' => env('RAZORPAY_CURRENCY', 'INR'),
    ],
];
