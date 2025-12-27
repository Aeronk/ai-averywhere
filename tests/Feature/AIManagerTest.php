<?php

namespace Katema\LaravelGenAI\Tests\Feature;

use Katema\LaravelGenAI\Tests\TestCase;
use Katema\LaravelGenAI\Facades\AI;
use Katema\LaravelGenAI\AIManager;

class AIManagerTest extends TestCase
{
    /** @test */
    public function it_can_access_ai_manager_via_facade()
    {
        $this->assertInstanceOf(AIManager::class, AI::getFacadeRoot());
    }

    /** @test */
    public function it_has_default_driver()
    {
        $driver = AI::driver();

        $this->assertNotNull($driver);
    }

    /** @test */
    public function it_can_switch_drivers()
    {
        $openai = AI::driver('openai');
        $claude = AI::driver('claude');

        $this->assertNotSame($openai, $claude);
    }

    /** @test */
    public function it_can_set_system_prompt()
    {
        $manager = AI::withSystemPrompt('You are a helpful assistant');

        $this->assertInstanceOf(AIManager::class, $manager);
    }

    /** @test */
    public function it_can_set_context()
    {
        $manager = AI::withContext(['user' => 'John']);

        $this->assertInstanceOf(AIManager::class, $manager);
    }

    /** @test */
    public function it_can_chain_methods()
    {
        $manager = AI::withSystemPrompt('You are helpful')
            ->withContext(['user' => 'Jane']);

        $this->assertInstanceOf(AIManager::class, $manager);
    }

    /** @test */
    public function it_can_reset_context()
    {
        $manager = AI::withContext(['user' => 'John'])
            ->fresh();

        $this->assertInstanceOf(AIManager::class, $manager);
    }
}
