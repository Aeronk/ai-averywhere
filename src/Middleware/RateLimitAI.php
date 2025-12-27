<?php

namespace Katema\LaravelGenAI\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Katema\LaravelGenAI\Exceptions\RateLimitExceededException;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if rate limiting is enabled
        if (!config('genai.rate_limits.enabled', true)) {
            return $next($request);
        }

        $user = auth()->user();
        $userId = $user ? $user->id : $request->ip();

        // Check per-minute request limit
        $this->checkRequestLimit($userId);

        // Check daily token limit (if user is authenticated)
        if ($user) {
            $this->checkTokenLimit($userId);
        }

        return $next($request);
    }

    /**
     * Check requests per minute limit.
     */
    protected function checkRequestLimit(string|int $key): void
    {
        $maxRequests = config('genai.rate_limits.max_requests_per_minute', 60);
        $rateLimitKey = "ai_rate_limit:{$key}";

        $executed = RateLimiter::attempt(
            $rateLimitKey,
            $maxRequests,
            function () {
                // Allow the request
            },
            60 // 1 minute decay
        );

        if (!$executed) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw new RateLimitExceededException(
                "Too many AI requests. Please try again in {$seconds} seconds."
            );
        }
    }

    /**
     * Check daily token usage limit.
     */
    protected function checkTokenLimit(int $userId): void
    {
        $maxTokens = config('genai.rate_limits.max_tokens_per_day', 100000);
        $cacheKey = "ai_tokens_used:{$userId}:" . now()->format('Y-m-d');

        $tokensUsed = Cache::get($cacheKey, 0);

        if ($tokensUsed >= $maxTokens) {
            throw new RateLimitExceededException(
                "Daily token limit exceeded. Limit: {$maxTokens} tokens."
            );
        }
    }

    /**
     * Increment token usage for a user.
     */
    public static function incrementTokens(int $userId, int $tokens): void
    {
        $cacheKey = "ai_tokens_used:{$userId}:" . now()->format('Y-m-d');

        $current = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $current + $tokens, now()->endOfDay());
    }
}
