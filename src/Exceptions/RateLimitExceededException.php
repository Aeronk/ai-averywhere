<?php

namespace Katema\LaravelGenAI\Exceptions;

use Exception;

class RateLimitExceededException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = "Rate limit exceeded")
    {
        parent::__construct($message);
    }
}
