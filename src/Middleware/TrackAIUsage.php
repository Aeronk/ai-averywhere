<?php

namespace Katema\LaravelGenAI\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackAIUsage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if tracking is enabled
        if (!config('genai.tracking.enabled', true)) {
            return $next($request);
        }

        // Store start time
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        // Calculate response time
        $responseTime = (microtime(true) - $startTime) * 1000; // in milliseconds

        // Track AI usage if this was an AI request
        $this->trackUsage($request, $response, $responseTime);

        return $response;
    }

    /**
     * Track AI usage in the database.
     */
    protected function trackUsage(Request $request, Response $response, float $responseTime): void
    {
        // Only track if database storage is enabled
        if (!config('genai.tracking.store_in_database', true)) {
            return;
        }

        try {
            // Get AI request data from request attributes (if set by AI service)
            $aiData = $request->attributes->get('ai_request_data');

            if (!$aiData) {
                return; // Not an AI request
            }

            DB::table('ai_requests')->insert([
                'user_id' => auth()->id(),
                'provider' => $aiData['provider'] ?? 'unknown',
                'model' => $aiData['model'] ?? 'unknown',
                'type' => $aiData['type'] ?? 'text',
                'prompt' => $aiData['prompt'] ?? null,
                'messages' => isset($aiData['messages']) ? json_encode($aiData['messages']) : null,
                'response' => config('genai.tracking.log_responses', false)
                    ? $aiData['response'] ?? null
                    : null,
                'tokens_used' => $aiData['tokens_used'] ?? 0,
                'input_tokens' => $aiData['input_tokens'] ?? 0,
                'output_tokens' => $aiData['output_tokens'] ?? 0,
                'cost' => $aiData['cost'] ?? 0,
                'options' => isset($aiData['options']) ? json_encode($aiData['options']) : null,
                'metadata' => isset($aiData['metadata']) ? json_encode($aiData['metadata']) : null,
                'status' => $aiData['status'] ?? 'success',
                'error_message' => $aiData['error_message'] ?? null,
                'response_time_ms' => round($responseTime),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to track AI usage: ' . $e->getMessage());
        }
    }
}
