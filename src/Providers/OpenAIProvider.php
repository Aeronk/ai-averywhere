<?php

namespace Katema\LaravelGenAI\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Katema\LaravelGenAI\Contracts\AIProvider;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;
use Katema\LaravelGenAI\Exceptions\AIProviderException;

class OpenAIProvider implements AIProvider
{
    protected Client $client;
    protected array $config;
    protected ?string $systemPrompt = null;
    protected array $context = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $config['base_url'] ?? 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ],
            'timeout' => 60,
        ]);
    }

    /**
     * Generate text completion.
     */
    public function text(string $prompt, array $options = []): AIResponse
    {
        $messages = [];

        // Add system prompt if set
        if ($this->systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $this->systemPrompt];
        }

        // Add context if set
        if (!empty($this->context)) {
            $contextMessage = "Context:\n" . json_encode($this->context, JSON_PRETTY_PRINT);
            $messages[] = ['role' => 'system', 'content' => $contextMessage];
        }

        // Add user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->chat($messages, $options);
    }

    /**
     * Generate chat completion.
     */
    public function chat(array $messages, array $options = []): AIResponse
    {
        $model = $options['model'] ?? $this->config['model'] ?? 'gpt-4-turbo-preview';

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? config('genai.defaults.temperature', 0.7),
            'max_tokens' => $options['max_tokens'] ?? config('genai.defaults.max_tokens', 2000),
            'top_p' => $options['top_p'] ?? config('genai.defaults.top_p', 1.0),
        ];

        try {
            $response = $this->client->post('chat/completions', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $data['usage']['total_tokens'] ?? 0;
            $cost = $this->calculateCost($model, $data['usage'] ?? []);

            return new AIResponse(
                content: $content,
                tokensUsed: $tokensUsed,
                cost: $cost,
                model: $model,
                provider: 'openai',
                raw: $data
            );
        } catch (GuzzleException $e) {
            throw AIProviderException::apiError('openai', $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Generate embeddings.
     */
    public function embed(string $text): array
    {
        $payload = [
            'input' => $text,
            'model' => 'text-embedding-ada-002',
        ];

        try {
            $response = $this->client->post('embeddings', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['data'][0]['embedding'] ?? [];
        } catch (GuzzleException $e) {
            throw AIProviderException::apiError('openai', $e->getMessage(), $e->getCode());
        }
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
     * Set context.
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);

        return $this;
    }

    /**
     * Calculate cost based on token usage.
     */
    protected function calculateCost(string $model, array $usage): float
    {
        $costs = config('genai.costs.openai', []);

        if (!isset($costs[$model])) {
            return 0.0;
        }

        $inputTokens = $usage['prompt_tokens'] ?? 0;
        $outputTokens = $usage['completion_tokens'] ?? 0;

        $inputCost = ($inputTokens / 1000) * $costs[$model]['input'];
        $outputCost = ($outputTokens / 1000) * $costs[$model]['output'];

        return round($inputCost + $outputCost, 6);
    }
}
