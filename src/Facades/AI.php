<?php

namespace Katema\LaravelGenAI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Katema\LaravelGenAI\DataTransferObjects\AIResponse text(string $prompt, array $options = [])
 * @method static \Katema\LaravelGenAI\DataTransferObjects\AIResponse chat(array $messages, array $options = [])
 * @method static array json(string $prompt, array $options = [])
 * @method static array embed(string $text)
 * @method static \Katema\LaravelGenAI\DataTransferObjects\AIResponse prompt(string $name, array $variables = [])
 * @method static \Katema\LaravelGenAI\AIManager withSystemPrompt(string $systemPrompt)
 * @method static \Katema\LaravelGenAI\AIManager withContext(array $context)
 * @method static \Katema\LaravelGenAI\AIManager fresh()
 * @method static \Katema\LaravelGenAI\Contracts\AIProvider driver(string $name = null)
 *
 * @see \Katema\LaravelGenAI\AIManager
 */
class AI extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'genai';
    }
}
