<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'nova_poshta' => [
        'api_key' => env('NOVA_POSHTA_API_KEY'),
        'api_url' => env('NOVA_POSHTA_API_URL', 'https://api.novaposhta.ua/v2.0/json/'),
    ],

    // ✅ Meta Pixel (Facebook Pixel)
    'meta_pixel' => [
        'enabled'          => env('META_PIXEL_ENABLED', false),
        'id'               => env('META_PIXEL_ID'),
        'default_currency' => env('META_PIXEL_DEFAULT_CURRENCY', 'UAH'),
    ],

];
