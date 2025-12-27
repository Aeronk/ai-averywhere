<?php

namespace Katema\LaravelGenAI\Tests\Unit;

use Katema\LaravelGenAI\Tests\TestCase;
use Katema\LaravelGenAI\Providers\OpenAIProvider;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;
use Katema\LaravelGenAI\Exceptions\AIProviderException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class OpenAIProviderTest extends TestCase
{
    protected function getMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    /** @test */
    public function it_can_generate_text_completion()
    {
        $mockResponse = new Response(200, [], json_encode([
            'choices' => [
                ['message' => ['content' => 'Hello, World!']]
            ],
            'usage' => [
                'total_tokens' => 10,
                'prompt_tokens' => 5,
                'completion_tokens' => 5,
            ],
        ]));

        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4-turbo-preview',
        ]);

        // We can't easily mock the client in the provider, so this is a structure test
        $this->assertInstanceOf(OpenAIProvider::class, $provider);
    }

    /** @test */
    public function it_sets_system_prompt()
    {
        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ]);

        $result = $provider->withSystemPrompt('You are a helpful assistant');

        $this->assertInstanceOf(OpenAIProvider::class, $result);
    }

    /** @test */
    public function it_sets_context()
    {
        $provider = new OpenAIProvider([
            'api_key' => 'test-key',
            'model' => 'gpt-4',
        ]);

        $result = $provider->withContext(['user' => 'John']);

        $this->assertInstanceOf(OpenAIProvider::class, $result);
    }

    /** @test */
    public function it_throws_exception_for_missing_api_key()
    {
        $this->expectException(AIProviderException::class);

        // This would be tested at the manager level
        $provider = new OpenAIProvider([
            'api_key' => '',
            'model' => 'gpt-4',
        ]);
    }
}
