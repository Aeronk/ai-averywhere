<?php

namespace Katema\LaravelGenAI\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Katema\LaravelGenAI\GenAIServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup if needed
    }

    /**
     * Get package providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            GenAIServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     */
    protected function getPackageAliases($app): array
    {
        return [
            'AI' => \Katema\LaravelGenAI\Facades\AI::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup test configuration
        $app['config']->set('genai.default', 'openai');
        $app['config']->set('genai.providers.openai', [
            'api_key' => 'test-key',
            'model' => 'gpt-4-turbo-preview',
            'base_url' => 'https://api.openai.com/v1/',
        ]);
        $app['config']->set('genai.providers.claude', [
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
            'base_url' => 'https://api.anthropic.com/v1/',
        ]);
    }
}
