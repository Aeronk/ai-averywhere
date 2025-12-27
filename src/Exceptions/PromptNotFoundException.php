<?php

namespace Katema\LaravelGenAI\Exceptions;

use Exception;

class PromptNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $promptName)
    {
        parent::__construct("Prompt [{$promptName}] not found.");
    }
}
