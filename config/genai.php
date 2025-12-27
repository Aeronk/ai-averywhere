<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used by the
    | application. You may change this to any of the providers defined in
    | the "providers" configuration array below.
    |
    */
    'default' => env('GENAI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the AI providers for your application. Each
    | provider can have its own API key, model, and other settings.
    |
    */
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],

        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
            'base_url' => env('CLAUDE_BASE_URL', 'https://api.anthropic.com/v1'),
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1'),
        ],

        'ollama' => [
            'model' => env('OLLAMA_MODEL', 'llama2'),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | These options will be used as defaults for all AI requests unless
    | overridden in specific requests.
    |
    */
    'defaults' => [
        'temperature' => 0.7,
        'max_tokens' => 2000,
        'top_p' => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Management
    |--------------------------------------------------------------------------
    |
    | Configure how prompts are stored and loaded.
    |
    */
    'prompts' => [
        'path' => resource_path('prompts'),
        'cache' => env('GENAI_CACHE_PROMPTS', true),
        'cache_ttl' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for AI API calls to prevent excessive usage.
    |
    */
    'rate_limits' => [
        'enabled' => env('GENAI_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => env('GENAI_MAX_REQUESTS_PER_MINUTE', 60),
        'max_tokens_per_day' => env('GENAI_MAX_TOKENS_PER_DAY', 100000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Safety & Validation
    |--------------------------------------------------------------------------
    |
    | Configure safety features and validation rules.
    |
    */
    'safety' => [
        'prompt_injection_detection' => env('GENAI_DETECT_PROMPT_INJECTION', true),
        'output_validation' => env('GENAI_VALIDATE_OUTPUT', true),
        'max_prompt_length' => env('GENAI_MAX_PROMPT_LENGTH', 10000),
        'max_response_length' => env('GENAI_MAX_RESPONSE_LENGTH', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Tracking
    |--------------------------------------------------------------------------
    |
    | Configure logging and usage tracking.
    |
    */
    'tracking' => [
        'enabled' => env('GENAI_TRACKING_ENABLED', true),
        'log_requests' => env('GENAI_LOG_REQUESTS', true),
        'log_responses' => env('GENAI_LOG_RESPONSES', false),
        'store_in_database' => env('GENAI_STORE_IN_DB', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Token costs per 1000 tokens for different models (in USD).
    |
    */
    'costs' => [
        'openai' => [
            'gpt-4-turbo-preview' => ['input' => 0.01, 'output' => 0.03],
            'gpt-4' => ['input' => 0.03, 'output' => 0.06],
            'gpt-3.5-turbo' => ['input' => 0.0005, 'output' => 0.0015],
        ],
        'claude' => [
            'claude-3-5-sonnet-20241022' => ['input' => 0.003, 'output' => 0.015],
            'claude-3-opus-20240229' => ['input' => 0.015, 'output' => 0.075],
            'claude-3-sonnet-20240229' => ['input' => 0.003, 'output' => 0.015],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Configure caching for AI responses.
    |
    */
    'cache' => [
        'enabled' => env('GENAI_CACHE_ENABLED', false),
        'ttl' => env('GENAI_CACHE_TTL', 3600),
        'driver' => env('GENAI_CACHE_DRIVER', 'redis'),
    ],
];
