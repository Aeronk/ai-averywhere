<?php

namespace Katema\LaravelGenAI\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Katema\LaravelGenAI\Facades\AI;
use Illuminate\Support\Collection;

class ProcessBatchRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $requests,
        public ?string $driver = null,
        public $callback = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $results = [];
        $ai = $this->driver ? AI::driver($this->driver) : AI::driver();

        foreach ($this->requests as $index => $request) {
            try {
                $response = match ($request['type'] ?? 'text') {
                    'text' => $ai->text(
                        $request['prompt'],
                        $request['options'] ?? []
                    ),
                    'chat' => $ai->chat(
                        $request['messages'],
                        $request['options'] ?? []
                    ),
                    'json' => $ai->json(
                        $request['prompt'],
                        $request['options'] ?? []
                    ),
                    'prompt' => $ai->prompt(
                        $request['name'],
                        $request['variables'] ?? []
                    ),
                    default => throw new \InvalidArgumentException("Invalid request type: {$request['type']}")
                };

                $results[$index] = [
                    'status' => 'success',
                    'response' => $response,
                    'request' => $request,
                ];

            } catch (\Exception $e) {
                $results[$index] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'request' => $request,
                ];

                \Log::error('Batch AI request failed', [
                    'index' => $index,
                    'request' => $request,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Calculate totals
        $summary = $this->calculateSummary($results);

        // Log completion
        \Log::info('Batch AI processing completed', [
            'total_requests' => count($this->requests),
            'successful' => $summary['successful'],
            'failed' => $summary['failed'],
            'total_tokens' => $summary['total_tokens'],
            'total_cost' => $summary['total_cost'],
        ]);

        // Execute callback if provided
        if ($this->callback) {
            call_user_func($this->callback, $results, $summary);
        }
    }

    /**
     * Calculate summary statistics.
     */
    protected function calculateSummary(array $results): array
    {
        $successful = 0;
        $failed = 0;
        $totalTokens = 0;
        $totalCost = 0;

        foreach ($results as $result) {
            if ($result['status'] === 'success') {
                $successful++;
                $totalTokens += $result['response']->tokensUsed ?? 0;
                $totalCost += $result['response']->cost ?? 0;
            } else {
                $failed++;
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total_tokens' => $totalTokens,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('ProcessBatchRequests job failed', [
            'total_requests' => count($this->requests),
            'exception' => $exception->getMessage(),
        ]);
    }
}
