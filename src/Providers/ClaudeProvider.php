<?php

namespace Katema\LaravelGenAI\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Katema\LaravelGenAI\Contracts\AIProvider;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;
use Katema\LaravelGenAI\Exceptions\AIProviderException;

class ClaudeProvider implements AIProvider
{
    protected Client $client;
    protected array $config;
    protected ?string $systemPrompt = null;
    protected array $context = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $config['base_url'] ?? 'https://api.anthropic.com/v1/',
            'headers' => [
                'x-api-key' => $config['api_key'],
                'anthropic-version' => '2023-06-01',
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

        // Add context if set
        if (!empty($this->context)) {
            $contextMessage = "Context:\n" . json_encode($this->context, JSON_PRETTY_PRINT);
            $messages[] = ['role' => 'user', 'content' => $contextMessage];
            $messages[] = ['role' => 'assistant', 'content' => 'I understand the context and will use it in my responses.'];
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
        $model = $options['model'] ?? $this->config['model'] ?? 'claude-3-5-sonnet-20241022';

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? config('genai.defaults.max_tokens', 2000),
            'temperature' => $options['temperature'] ?? config('genai.defaults.temperature', 0.7),
        ];

        // Add system prompt if set
        if ($this->systemPrompt) {
            $payload['system'] = $this->systemPrompt;
        }

        try {
            $response = $this->client->post('messages', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $content = $data['content'][0]['text'] ?? '';
            $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);
            $cost = $this->calculateCost($model, $data['usage'] ?? []);

            return new AIResponse(
                content: $content,
                tokensUsed: $tokensUsed,
                cost: $cost,
                model: $model,
                provider: 'claude',
                raw: $data
            );
        } catch (GuzzleException $e) {
            $errorBody = '';
            if ($e->hasResponse()) {
                $errorBody = $e->getResponse()->getBody()->getContents();
            }

            throw AIProviderException::apiError('claude', $e->getMessage() . ' - ' . $errorBody, $e->getCode());
        }
    }

    /**
     * Generate embeddings (not supported by Claude).
     */
    public function embed(string $text): array
    {
        throw new \RuntimeException('Embeddings are not supported by Claude provider');
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
        $costs = config('genai.costs.claude', []);

        if (!isset($costs[$model])) {
            return 0.0;
        }

        $inputTokens = $usage['input_tokens'] ?? 0;
        $outputTokens = $usage['output_tokens'] ?? 0;

        $inputCost = ($inputTokens / 1000) * $costs[$model]['input'];
        $outputCost = ($outputTokens / 1000) * $costs[$model]['output'];

        return round($inputCost + $outputCost, 6);
    }
}
