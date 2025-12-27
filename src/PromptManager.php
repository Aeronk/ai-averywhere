<?php

namespace Katema\LaravelGenAI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Katema\LaravelGenAI\Exceptions\PromptNotFoundException;

class PromptManager
{
    protected string $promptsPath;
    protected bool $cacheEnabled;

    public function __construct(string $promptsPath, bool $cacheEnabled = true)
    {
        $this->promptsPath = $promptsPath;
        $this->cacheEnabled = $cacheEnabled;
    }

    /**
     * Render a prompt with variables.
     */
    public function render(string $name, array $variables = []): string
    {
        $prompt = $this->load($name);

        return $this->interpolate($prompt, $variables);
    }

    /**
     * Load a prompt from file.
     */
    public function load(string $name): string
    {
        if ($this->cacheEnabled) {
            return Cache::remember(
                "genai.prompt.{$name}",
                config('genai.prompts.cache_ttl', 3600),
                fn() => $this->loadFromFile($name)
            );
        }

        return $this->loadFromFile($name);
    }

    /**
     * Load prompt from file system.
     */
    protected function loadFromFile(string $name): string
    {
        $path = $this->getPromptPath($name);

        if (!File::exists($path)) {
            throw new PromptNotFoundException($name);
        }

        return File::get($path);
    }

    /**
     * Get the file path for a prompt.
     */
    protected function getPromptPath(string $name): string
    {
        // Convert dot notation to path (e.g., 'marketing.email' => 'marketing/email.md')
        $path = str_replace('.', DIRECTORY_SEPARATOR, $name);

        return $this->promptsPath . DIRECTORY_SEPARATOR . $path . '.md';
    }

    /**
     * Interpolate variables into prompt.
     */
    protected function interpolate(string $prompt, array $variables): string
    {
        foreach ($variables as $key => $value) {
            // Convert value to string if it's an array or object
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }

            // Replace {{variable}} with value
            $prompt = str_replace("{{{$key}}}", $value, $prompt);
        }

        return $prompt;
    }

    /**
     * List all available prompts.
     */
    public function list(): array
    {
        if (!File::exists($this->promptsPath)) {
            return [];
        }

        $files = File::allFiles($this->promptsPath);
        $prompts = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                $relativePath = str_replace($this->promptsPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $name = str_replace(['/', '\\', '.md'], ['.', '.', ''], $relativePath);
                $prompts[] = $name;
            }
        }

        return $prompts;
    }

    /**
     * Check if a prompt exists.
     */
    public function exists(string $name): bool
    {
        return File::exists($this->getPromptPath($name));
    }

    /**
     * Clear prompt cache.
     */
    public function clearCache(string $name = null): void
    {
        if ($name) {
            Cache::forget("genai.prompt.{$name}");
        } else {
            // Clear all prompt caches
            foreach ($this->list() as $promptName) {
                Cache::forget("genai.prompt.{$promptName}");
            }
        }
    }
}
