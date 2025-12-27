<?php

namespace Katema\LaravelGenAI\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Katema\LaravelGenAI\Facades\AI;
use Illuminate\Database\Eloquent\Model;

class GenerateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Model $model,
        public string $field,
        public string $prompt,
        public array $variables = [],
        public array $options = []
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if using a prompt template or direct text
            if (str_starts_with($this->prompt, 'prompt:')) {
                $promptName = substr($this->prompt, 7);
                $response = AI::prompt($promptName, $this->variables);
            } else {
                $response = AI::text($this->prompt, $this->options);
            }

            // Update the model field
            $this->model->update([
                $this->field => $response->content,
            ]);

            // Optionally store metadata
            if (isset($this->options['store_metadata']) && $this->options['store_metadata']) {
                $metadataField = $this->field . '_metadata';

                if ($this->model->isFillable($metadataField)) {
                    $this->model->update([
                        $metadataField => [
                            'tokens_used' => $response->tokensUsed,
                            'cost' => $response->cost,
                            'model' => $response->model,
                            'provider' => $response->provider,
                            'generated_at' => now(),
                        ],
                    ]);
                }
            }

        } catch (\Exception $e) {
            // Log the error
            \Log::error('AI content generation failed', [
                'model' => get_class($this->model),
                'id' => $this->model->getKey(),
                'field' => $this->field,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('GenerateContent job failed', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
            'field' => $this->field,
            'exception' => $exception->getMessage(),
        ]);
    }
}
