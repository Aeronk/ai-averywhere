<?php

namespace Katema\LaravelGenAI\Exceptions;

use Exception;

class AIProviderException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message = "AI Provider Error", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for API errors.
     */
    public static function apiError(string $provider, string $error, int $statusCode = 0): self
    {
        return new self(
            "AI Provider [{$provider}] API Error: {$error}",
            $statusCode
        );
    }

    /**
     * Create exception for configuration errors.
     */
    public static function configurationError(string $provider, string $error): self
    {
        return new self(
            "AI Provider [{$provider}] Configuration Error: {$error}"
        );
    }
}
