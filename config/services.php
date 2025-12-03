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

    'sms' => [
        'panel' => env('SMS_PANEL', 'iranpayamak'), // 'farazsms' or 'iranpayamak'
        'api_url' => env('SMS_API_URL', 'https://edge.ippanel.com/v1'),
        'token' => env('SMS_TOKEN', 'YTA3ZDgzYjktZDYxNS00ZGM0LWIwOTctMGViN2Q4ZWY0ZGYxN2VhMzE4NTEzNWRhMGQxZGI0NjBmY2MwODU2YThkZTA='),
        'from_number' => env('SMS_FROM_NUMBER', '+98PRO'),
        // Iranpayamak configuration
        'iranpayamak' => [
            'base_url' => env('IRANPAYAMAK_BASE_URL', 'https://api.iranpayamak.com/'),
            'api_key' => env('IRANPAYAMAK_API_KEY', ''),
            'line_number' => env('IRANPAYAMAK_LINE_NUMBER', 'PRO'),
            'number_format' => env('IRANPAYAMAK_NUMBER_FORMAT', 'persian'),
        ],
    ],

];
