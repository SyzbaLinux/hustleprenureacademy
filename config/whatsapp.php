<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp Business API integration using Meta's
    | Graph API.
    |
    */

    'graph_api_token' => env('GRAPH_API_TOKEN'),

    'business_phone_number_id' => env('BUSINESS_PHONE_NUMBER_ID'),

    'webhook_verify_token' => env('WEBHOOK_VERIFY_TOKEN'),

    'api_version' => env('GRAPH_API_VERSION', 'v22.0'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | WhatsApp API rate limits to prevent hitting API quotas.
    |
    */

    'rate_limit' => [
        'messages_per_second' => env('WHATSAPP_RATE_LIMIT_PER_SECOND', 20),
        'burst_limit' => env('WHATSAPP_BURST_LIMIT', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation Timeout
    |--------------------------------------------------------------------------
    |
    | Time in minutes before a conversation flow expires.
    |
    */

    'conversation_timeout' => env('WHATSAPP_CONVERSATION_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | Default language for messages.
    |
    */

    'default_language' => env('WHATSAPP_DEFAULT_LANGUAGE', 'en'),
];
