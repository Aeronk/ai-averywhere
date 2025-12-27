<?php

namespace Katema\LaravelGenAI\Exceptions;

use Exception;

class ValidationException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = "Validation failed")
    {
        parent::__construct($message);
    }
}
