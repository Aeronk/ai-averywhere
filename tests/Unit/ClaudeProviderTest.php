<?php

namespace Katema\LaravelGenAI\Tests\Unit;

use Katema\LaravelGenAI\Tests\TestCase;
use Katema\LaravelGenAI\Providers\ClaudeProvider;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;

class ClaudeProviderTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $provider = new ClaudeProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $this->assertInstanceOf(ClaudeProvider::class, $provider);
    }

    /** @test */
    public function it_sets_system_prompt()
    {
        $provider = new ClaudeProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $result = $provider->withSystemPrompt('You are a helpful assistant');

        $this->assertInstanceOf(ClaudeProvider::class, $result);
    }

    /** @test */
    public function it_sets_context()
    {
        $provider = new ClaudeProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $result = $provider->withContext(['user' => 'Jane']);

        $this->assertInstanceOf(ClaudeProvider::class, $result);
    }

    /** @test */
    public function it_throws_exception_for_embeddings()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Embeddings are not supported by Claude provider');

        $provider = new ClaudeProvider([
            'api_key' => 'test-key',
            'model' => 'claude-3-5-sonnet-20241022',
        ]);

        $provider->embed('test text');
    }
}
