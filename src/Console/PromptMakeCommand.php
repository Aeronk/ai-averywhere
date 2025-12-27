<?php

namespace Katema\LaravelGenAI\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PromptMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'genai:prompt {name : The name of the prompt (e.g., marketing.email)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new AI prompt template';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $path = $this->getPromptPath($name);

        // Check if prompt already exists
        if (File::exists($path)) {
            $this->error("Prompt [{$name}] already exists!");
            return Command::FAILURE;
        }

        // Create directory if it doesn't exist
        $directory = dirname($path);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Create prompt template
        $stub = $this->getStub();
        $content = str_replace(
            ['{{name}}', '{{title}}'],
            [$name, Str::title(str_replace(['.', '_', '-'], ' ', $name))],
            $stub
        );

        File::put($path, $content);

        $this->info("Prompt created successfully!");
        $this->line("Location: {$path}");
        $this->newLine();
        $this->comment("Edit your prompt template and use it with:");
        $this->line("  AI::prompt('{$name}', ['variable' => 'value'])");

        return Command::SUCCESS;
    }

    /**
     * Get the prompt file path.
     */
    protected function getPromptPath(string $name): string
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $name);
        return resource_path('prompts' . DIRECTORY_SEPARATOR . $path . '.md');
    }

    /**
     * Get the stub content.
     */
    protected function getStub(): string
    {
        return <<<'STUB'
# {{title}}

Write a detailed prompt describing what you want the AI to generate.

**Input Variables:**
- {{variable1}}: Description of variable1
- {{variable2}}: Description of variable2

**Requirements:**
- List your requirements here
- Be specific about tone, length, format
- Include any constraints or guidelines

**Example Output:**
Describe what the expected output should look like.
STUB;
    }
}
