<?php

namespace Katema\LaravelGenAI\Contracts;

use Katema\LaravelGenAI\DataTransferObjects\AIResponse;

interface AIProvider
{
    /**
     * Generate text completion from a prompt.
     *
     * @param string $prompt The prompt to generate from
     * @param array $options Additional options (temperature, max_tokens, etc.)
     * @return AIResponse
     */
    public function text(string $prompt, array $options = []): AIResponse;

    /**
     * Generate chat completion from messages.
     *
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param array $options Additional options
     * @return AIResponse
     */
    public function chat(array $messages, array $options = []): AIResponse;

    /**
     * Generate embeddings for text.
     *
     * @param string $text The text to embed
     * @return array Vector embeddings
     */
    public function embed(string $text): array;

    /**
     * Set the system prompt for the conversation.
     *
     * @param string $systemPrompt
     * @return self
     */
    public function withSystemPrompt(string $systemPrompt): self;

    /**
     * Set context for the AI provider.
     *
     * @param array $context
     * @return self
     */
    public function withContext(array $context): self;
}
