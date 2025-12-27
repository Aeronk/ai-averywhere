<?php

namespace Katema\LaravelGenAI\Tests\Unit;

use Katema\LaravelGenAI\Tests\TestCase;
use Katema\LaravelGenAI\PromptManager;
use Katema\LaravelGenAI\Exceptions\PromptNotFoundException;
use Illuminate\Support\Facades\File;

class PromptManagerTest extends TestCase
{
    protected string $testPromptsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testPromptsPath = __DIR__ . '/../fixtures/prompts';

        // Create test prompts directory
        if (!File::exists($this->testPromptsPath)) {
            File::makeDirectory($this->testPromptsPath, 0755, true);
        }

        // Create a test prompt
        File::put(
            $this->testPromptsPath . '/test.md',
            'Hello {{name}}, welcome to {{product}}!'
        );
    }

    protected function tearDown(): void
    {
        // Clean up test prompts
        if (File::exists($this->testPromptsPath)) {
            File::deleteDirectory(dirname($this->testPromptsPath));
        }

        parent::tearDown();
    }

    /** @test */
    public function it_can_load_a_prompt()
    {
        $manager = new PromptManager($this->testPromptsPath, false);

        $prompt = $manager->load('test');

        $this->assertEquals('Hello {{name}}, welcome to {{product}}!', $prompt);
    }

    /** @test */
    public function it_can_interpolate_variables()
    {
        $manager = new PromptManager($this->testPromptsPath, false);

        $result = $manager->render('test', [
            'name' => 'John',
            'product' => 'Laravel GenAI',
        ]);

        $this->assertEquals('Hello John, welcome to Laravel GenAI!', $result);
    }

    /** @test */
    public function it_throws_exception_for_missing_prompt()
    {
        $this->expectException(PromptNotFoundException::class);

        $manager = new PromptManager($this->testPromptsPath, false);
        $manager->load('nonexistent');
    }

    /** @test */
    public function it_can_list_prompts()
    {
        $manager = new PromptManager($this->testPromptsPath, false);

        $prompts = $manager->list();

        $this->assertContains('test', $prompts);
    }

    /** @test */
    public function it_can_check_if_prompt_exists()
    {
        $manager = new PromptManager($this->testPromptsPath, false);

        $this->assertTrue($manager->exists('test'));
        $this->assertFalse($manager->exists('nonexistent'));
    }
}
