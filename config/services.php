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
    'apis' => [
        4 => [
            'base_url' => env('API_UAMEX_BASE_URL', 'https://unitedmex.mindware.com.mx'),
            'token' => env('API_UAMEX_TOKEN', ''),
        ],
        1 => [
            'base_url' => env('API_UACUN_BASE_URL', 'https://unitedmex.test.mindware.com.mx'),
            'token' => env('API_UACUN_TOKEN', ''),
        ],
        5 => [
            'base_url' => env('API_UAPVR_BASE_URL', 'https://unitedpvr.mindware.com.mx'),
            'token' => env('API_UAPVR_TOKEN', ''),
        ],
        6 => [
            'base_url' => env('API_UASJD_BASE_URL', 'https://unitedsjd.mindware.com.mx'),
            'token' => env('API_UASJD_TOKEN', ''),
        ],
    ],
];
