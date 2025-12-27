<?php

namespace Katema\LaravelGenAI\Console;

use Illuminate\Console\Command;
use Katema\LaravelGenAI\PromptManager;
use Katema\LaravelGenAI\Facades\AI;

class TestPromptCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'genai:test {prompt : The prompt name to test} 
                                        {--var=* : Variables in key=value format}
                                        {--provider= : AI provider to use}';

    /**
     * The console command description.
     */
    protected $description = 'Test an AI prompt template';

    /**
     * Execute the console command.
     */
    public function handle(PromptManager $promptManager): int
    {
        $promptName = $this->argument('prompt');
        $provider = $this->option('provider');

        // Check if prompt exists
        if (!$promptManager->exists($promptName)) {
            $this->error("Prompt [{$promptName}] not found!");
            $this->newLine();
            $this->comment('Available prompts:');
            foreach ($promptManager->list() as $name) {
                $this->line("  - {$name}");
            }
            return Command::FAILURE;
        }

        // Parse variables
        $variables = $this->parseVariables($this->option('var'));

        try {
            $this->info("Testing prompt: {$promptName}");
            $this->newLine();

            // Show rendered prompt
            $renderedPrompt = $promptManager->render($promptName, $variables);
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->comment('Rendered Prompt:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line($renderedPrompt);
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            // Ask if user wants to execute
            if (!$this->confirm('Execute this prompt with AI?', true)) {
                return Command::SUCCESS;
            }

            $this->info('Generating AI response...');
            $this->newLine();

            // Execute with AI
            $ai = $provider ? AI::driver($provider) : AI::driver();
            $response = $ai->prompt($promptName, $variables);

            // Display response
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('AI Response:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line($response->content);
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            // Display metadata
            $this->comment('Metadata:');
            $this->line("  Provider: {$response->provider}");
            $this->line("  Model: {$response->model}");
            $this->line("  Tokens: {$response->tokensUsed}");
            $this->line("  Cost: $" . number_format($response->cost, 4));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Parse variable options into array.
     */
    protected function parseVariables(array $varOptions): array
    {
        $variables = [];

        foreach ($varOptions as $option) {
            if (str_contains($option, '=')) {
                [$key, $value] = explode('=', $option, 2);
                $variables[trim($key)] = trim($value);
            }
        }

        return $variables;
    }
}
