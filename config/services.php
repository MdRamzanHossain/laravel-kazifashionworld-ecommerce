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

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from'  => env('TWILIO_NUMBER'),
    ],

    'local_sms' => [
        'url'     => env('SMS_GATEWAY_URL'),
        'api_key' => env('SMS_GATEWAY_API_KEY'),
    ],

    'sms_default_gateway' => env('SMS_DEFAULT_GATEWAY', 'local_sms'),

    'sslcommerz' => [
        'store_id'       => env('SSLCZ_STORE_ID', 'testbox'),
        'store_password' => env('SSLCZ_STORE_PASSWORD', 'qwerty'),
        'sandbox_mode'   => env('SSLCZ_SANDBOX_MODE', true),
        'currency'       => env('SSLCZ_CURRENCY', 'BDT'),
    ],

    'bkash' => [
        'app_key'      => env('BKASH_APP_KEY', '4f6oComplexityKey123'),
        'app_secret'   => env('BKASH_APP_SECRET', '2is7SecretToken456'),
        'username'     => env('BKASH_USERNAME', 'sandboxTestUser'),
        'password'     => env('BKASH_PASSWORD', 'sandboxTestPass'),
        'sandbox_mode' => env('BKASH_SANDBOX_MODE', true),
        'currency'     => env('BKASH_CURRENCY', 'BDT'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI', env('APP_URL', 'http://localhost:8000') . '/auth/facebook/callback'),
    ],

];


