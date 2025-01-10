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
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN', 'bot_token'),
        'chat_id' => env('TELEGRAM_CHAT_ID', 'chat_id'),
        'topic_id_new_token' => env('TELEGRAM_TOPIC_NEWTOKEN', 'topic_id_new_token'),
        'topic_id_token_boosts' => env('TELEGRAM_TOPIC_TOKENBOOSTS', 'topic_id_token_boosts'),
        'topic_id_token_trade' => env('TELEGRAM_TOPIC_TOKENTRADE', 'topic_id_token_trade'),
        'topic_id_token_boosts_other' => env('TELEGRAM_TOPIC_TOKENBOOSTS_OTHER', 'topic_id_token_boosts_other'),
        'topic_id_new_token_pumpfun' => env('TELEGRAM_TOPIC_NEWTOKEN_PUMPFUN', 'topic_id_new_token_pumpfun'),
    ],

];
