<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure AI features for Laravel Ultra
    |
    */

    'ai' => [
        'enabled' => env('ULTRA_AI_ENABLED', true),
        'provider' => env('ULTRA_AI_PROVIDER', 'openai'),
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('ULTRA_AI_MODEL', 'gpt-4'),
        ],
        'auto_configure' => true,
        'suggest_columns' => true,
        'optimize_queries' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-time Configuration
    |--------------------------------------------------------------------------
    |
    | Configure real-time features
    |
    */

    'realtime' => [
        'enabled' => env('ULTRA_REALTIME_ENABLED', false),
        'broadcaster' => env('ULTRA_BROADCASTER', 'pusher'),
        'collaboration' => [
            'enabled' => true,
            'cursors' => true,
            'comments' => true,
            'presence' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Configuration
    |--------------------------------------------------------------------------
    |
    | Configure frontend framework preferences
    |
    */

    'frontend' => [
        'framework' => env('ULTRA_FRONTEND_FRAMEWORK', 'vue'),
        'vue' => [
            'version' => 3,
            'composition_api' => true,
        ],
        'react' => [
            'version' => 18,
        ],
        'ssr' => [
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | Default table configurations
    |
    */

    'table' => [
        'default_per_page' => 25,
        'max_per_page' => 100,
        'views' => ['table', 'kanban', 'calendar'],
        'export' => [
            'enabled' => true,
            'formats' => ['csv', 'excel', 'pdf'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Configuration
    |--------------------------------------------------------------------------
    |
    | Default form configurations
    |
    */

    'form' => [
        'auto_save' => true,
        'validation' => [
            'live' => true,
            'ai_enhanced' => true,
        ],
        'voice_input' => [
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Styling Configuration
    |--------------------------------------------------------------------------
    |
    | UI styling preferences
    |
    */

    'styling' => [
        'theme' => 'default',
        'dark_mode' => true,
        'css_framework' => 'tailwind', // tailwind, bootstrap, or custom
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Usage analytics and insights
    |
    */

    'analytics' => [
        'enabled' => env('ULTRA_ANALYTICS_ENABLED', true),
        'track_interactions' => true,
        'track_performance' => true,
        'privacy' => 'anonymized',
    ],

];