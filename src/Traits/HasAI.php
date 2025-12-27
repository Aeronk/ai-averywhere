<?php

namespace Katema\LaravelGenAI\Traits;

use Katema\LaravelGenAI\Facades\AI;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;

trait HasAI
{
    /**
     * Get an AI instance with model context.
     */
    public function ai(): static
    {
        AI::withContext($this->getAIContext());

        return $this;
    }

    /**
     * Generate a summary of the model.
     */
    public function summarize(int $maxWords = 100): AIResponse
    {
        $context = $this->getAIContext();

        $prompt = "Summarize the following " . class_basename($this) . " in approximately {$maxWords} words:\n\n";
        $prompt .= json_encode($context, JSON_PRETTY_PRINT);

        return AI::withContext($context)->text($prompt);
    }

    /**
     * Generate a description of the model.
     */
    public function describe(string $focus = null): AIResponse
    {
        $context = $this->getAIContext();

        $prompt = "Write a detailed description of this " . class_basename($this);

        if ($focus) {
            $prompt .= " focusing on: {$focus}";
        }

        $prompt .= "\n\nData:\n" . json_encode($context, JSON_PRETTY_PRINT);

        return AI::withContext($context)->text($prompt);
    }

    /**
     * Ask questions or get insights about the model.
     */
    public function insights(string $question): AIResponse
    {
        $context = $this->getAIContext();

        $prompt = "Based on this " . class_basename($this) . " data:\n\n";
        $prompt .= json_encode($context, JSON_PRETTY_PRINT);
        $prompt .= "\n\nQuestion: {$question}";

        return AI::withContext($context)->text($prompt);
    }

    /**
     * Generate content using the model as context.
     */
    public function generate(string $instruction, array $options = []): AIResponse
    {
        $context = $this->getAIContext();

        $prompt = $instruction . "\n\n";
        $prompt .= "Context:\n" . json_encode($context, JSON_PRETTY_PRINT);

        return AI::withContext($context)->text($prompt, $options);
    }

    /**
     * Get context data for AI requests.
     * Override this method in your model to customize the context.
     */
    protected function getAIContext(): array
    {
        // Get visible attributes
        $attributes = $this->getAttributes();

        // Remove timestamps if not needed
        unset($attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);

        // Add model name
        $context = [
            'model' => class_basename($this),
            'id' => $this->getKey(),
            'attributes' => $attributes,
        ];

        // Include loaded relationships
        if ($this->relationLoaded('user')) {
            $context['user'] = $this->user->name ?? $this->user->email ?? null;
        }

        return $context;
    }
}
