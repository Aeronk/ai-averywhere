<?php

namespace Katema\LaravelGenAI\Tests\Feature;

use Katema\LaravelGenAI\Tests\TestCase;
use Katema\LaravelGenAI\DataTransferObjects\AIResponse;

class AIResponseTest extends TestCase
{
    /** @test */
    public function it_can_create_response()
    {
        $response = new AIResponse(
            content: 'Hello, World!',
            tokensUsed: 10,
            cost: 0.0001,
            model: 'gpt-4',
            provider: 'openai',
            raw: ['test' => 'data']
        );

        $this->assertEquals('Hello, World!', $response->content);
        $this->assertEquals(10, $response->tokensUsed);
        $this->assertEquals(0.0001, $response->cost);
        $this->assertEquals('gpt-4', $response->model);
        $this->assertEquals('openai', $response->provider);
    }

    /** @test */
    public function it_can_convert_to_array()
    {
        $response = new AIResponse(
            content: 'Test content',
            tokensUsed: 5,
            cost: 0.0001
        );

        $array = $response->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Test content', $array['content']);
        $this->assertEquals(5, $array['tokens_used']);
    }

    /** @test */
    public function it_can_convert_to_json()
    {
        $response = new AIResponse(
            content: 'Test',
            tokensUsed: 1
        );

        $json = $response->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('Test', $decoded['content']);
    }

    /** @test */
    public function it_can_convert_to_string()
    {
        $response = new AIResponse(content: 'Hello');

        $this->assertEquals('Hello', (string) $response);
    }
}
