<?php

namespace Katema\LaravelGenAI;

use Illuminate\Support\Manager;
use Katema\LaravelGenAI\Contracts\AIProvider;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;
use Katema\LaravelGenAI\Providers\OpenAIProvider;
use Katema\LaravelGenAI\Providers\ClaudeProvider;
use Katema\LaravelGenAI\Exceptions\AIProviderException;

class AIManager extends Manager
{
    protected array $context = [];
    protected ?string $systemPrompt = null;

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('genai.default', 'openai');
    }

    /**
     * Create OpenAI driver.
     */
    public function createOpenaiDriver(): AIProvider
    {
        $config = $this->config->get('genai.providers.openai');

        if (empty($config['api_key'])) {
            throw AIProviderException::configurationError('openai', 'API key not configured');
        }

        return new OpenAIProvider($config);
    }

    /**
     * Create Claude driver.
     */
    public function createClaudeDriver(): AIProvider
    {
        $config = $this->config->get('genai.providers.claude');

        if (empty($config['api_key'])) {
            throw AIProviderException::configurationError('claude', 'API key not configured');
        }

        return new ClaudeProvider($config);
    }

    /**
     * Generate text from a prompt.
     */
    public function text(string $prompt, array $options = []): AIResponse
    {
        $provider = $this->applyContext($this->driver());

        return $provider->text($prompt, $options);
    }

    /**
     * Generate chat completion.
     */
    public function chat(array $messages, array $options = []): AIResponse
    {
        $provider = $this->applyContext($this->driver());

        return $provider->chat($messages, $options);
    }

    /**
     * Generate JSON structured output.
     */
    public function json(string $prompt, array $options = []): array
    {
        $response = $this->text($prompt, $options);

        // Try to extract JSON from the response
        $content = $response->content;

        // Remove markdown code blocks if present
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);

        $data = json_decode(trim($content), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse JSON response: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Generate embeddings.
     */
    public function embed(string $text): array
    {
        return $this->driver()->embed($text);
    }

    /**
     * Use a named prompt template.
     */
    public function prompt(string $name, array $variables = []): AIResponse
    {
        $promptManager = $this->container->make(PromptManager::class);
        $prompt = $promptManager->render($name, $variables);

        return $this->text($prompt);
    }

    /**
     * Set system prompt.
     */
    public function withSystemPrompt(string $systemPrompt): self
    {
        $this->systemPrompt = $systemPrompt;

        return $this;
    }

    /**
     * Set context for AI requests.
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Clear context and system prompt (fresh instance).
     */
    public function fresh(): self
    {
        $this->context = [];
        $this->systemPrompt = null;

        return $this;
    }

    /**
     * Apply context and system prompt to provider.
     */
    protected function applyContext(AIProvider $provider): AIProvider
    {
        if (!empty($this->context)) {
            $provider->withContext($this->context);
        }

        if ($this->systemPrompt) {
            $provider->withSystemPrompt($this->systemPrompt);
        }

        return $provider;
    }
}
