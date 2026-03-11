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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'signhost' => [
        'base_url' => env('SIGNHOST_BASE_URL', 'https://api.signhost.com/api'),
        'application_key' => env('SIGNHOST_APPLICATION_KEY'),
        'user_token' => env('SIGNHOST_USER_TOKEN'),
        'postback_url' => env('SIGNHOST_POSTBACK_URL'),
        'postback_code' => env('SIGNHOST_POSTBACK_CODE'),
        'webhook_bearer_token' => env('SIGNHOST_WEBHOOK_BEARER_TOKEN'),
    ],

];
