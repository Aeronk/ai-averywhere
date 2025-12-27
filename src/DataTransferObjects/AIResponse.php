<?php

namespace Katema\LaravelGenAI\DataTransferObjects;

class AIResponse
{
    /**
     * Create a new AI response instance.
     *
     * @param string $content The generated content
     * @param int $tokensUsed Number of tokens used
     * @param float $cost Estimated cost in USD
     * @param string $model Model used
     * @param string $provider Provider name
     * @param array $raw Raw API response
     */
    public function __construct(
        public readonly string $content,
        public readonly int $tokensUsed = 0,
        public readonly float $cost = 0.0,
        public readonly string $model = '',
        public readonly string $provider = '',
        public readonly array $raw = []
    ) {
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'tokens_used' => $this->tokensUsed,
            'cost' => $this->cost,
            'model' => $this->model,
            'provider' => $this->provider,
            'raw' => $this->raw,
        ];
    }

    /**
     * Convert to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Get content as string.
     */
    public function __toString(): string
    {
        return $this->content;
    }
}
