<?php

namespace Katema\LaravelGenAI;

use Illuminate\Support\ServiceProvider;
use Katema\LaravelGenAI\Console\InstallCommand;
use Katema\LaravelGenAI\Console\PromptMakeCommand;
use Katema\LaravelGenAI\Console\TestPromptCommand;

class GenAIServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/genai.php',
            'genai'
        );

        // Register the main AIManager as a singleton
        $this->app->singleton('genai', function ($app) {
            return new AIManager($app);
        });

        // Register PromptManager
        $this->app->singleton(PromptManager::class, function ($app) {
            return new PromptManager(
                config('genai.prompts.path'),
                config('genai.prompts.cache', true)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/genai.php' => config_path('genai.php'),
        ], 'genai-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'genai-migrations');

        // Publish example prompts
        $this->publishes([
            __DIR__ . '/../resources/prompts/' => resource_path('prompts'),
        ], 'genai-prompts');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PromptMakeCommand::class,
                TestPromptCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['genai', PromptManager::class];
    }
}
