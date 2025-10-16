<?php

return [

    'ai' => [
        'enabled' => env('ULTRA_AI_ENABLED', false),
        'provider' => env('ULTRA_AI_PROVIDER', 'openai'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('ULTRA_AI_MODEL', 'gpt-3.5-turbo'),
        ],
    ],

    'realtime' => [
        'enabled' => env('ULTRA_REALTIME_ENABLED', false),
        'broadcaster' => env('ULTRA_BROADCASTER', 'pusher'),
    ],

    'frontend' => [
        'framework' => env('ULTRA_FRONTEND_FRAMEWORK', 'vue'),
    ],

    'table' => [
        'default_per_page' => 25,
        'max_per_page' => 100,
    ],

    'form' => [
        'auto_save' => true,
        'live_validation' => true,
    ],

];