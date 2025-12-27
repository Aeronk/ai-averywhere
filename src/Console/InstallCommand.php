<?php

namespace Katema\LaravelGenAI\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'genai:install';

    /**
     * The console command description.
     */
    protected $description = 'Install Laravel GenAI package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Laravel GenAI...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'genai-config',
            '--force' => $this->option('verbose') ? true : false,
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'genai-migrations',
            '--force' => $this->option('verbose') ? true : false,
        ]);

        // Publish example prompts
        $this->call('vendor:publish', [
            '--tag' => 'genai-prompts',
        ]);

        // Create prompts directory if it doesn't exist
        $promptsPath = resource_path('prompts');
        if (!File::exists($promptsPath)) {
            File::makeDirectory($promptsPath, 0755, true);
            $this->info('Created prompts directory at: ' . $promptsPath);
        }

        // Run migrations
        if ($this->confirm('Would you like to run migrations now?', true)) {
            $this->call('migrate');
        }

        // Display environment variables instructions
        $this->displayEnvInstructions();

        $this->newLine();
        $this->info('✓ Laravel GenAI installed successfully!');
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Display environment variable setup instructions.
     */
    protected function displayEnvInstructions(): void
    {
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Environment Configuration');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->comment('Add the following to your .env file:');
        $this->newLine();

        $envVars = [
            'GENAI_PROVIDER=openai',
            '',
            '# OpenAI Configuration',
            'OPENAI_API_KEY=your-openai-api-key',
            'OPENAI_MODEL=gpt-4-turbo-preview',
            '',
            '# Claude Configuration (optional)',
            'CLAUDE_API_KEY=your-claude-api-key',
            'CLAUDE_MODEL=claude-3-5-sonnet-20241022',
        ];

        foreach ($envVars as $line) {
            if (empty($line)) {
                $this->newLine();
            } else {
                $this->line('  ' . $line);
            }
        }

        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
