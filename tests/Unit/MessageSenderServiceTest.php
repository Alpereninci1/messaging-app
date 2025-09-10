<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Services\MessageSenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MessageSenderServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageSenderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Redis'i mock'la
        Redis::shouldReceive('set')->andReturn(true);
        
        $this->service = new MessageSenderService();
    }

    /** @test */
    public function it_throws_exception_for_content_exceeding_500_characters()
    {
        $message = new Message([
            'to' => '+905551111111',
            'content' => str_repeat('a', 501), // 501 karakter
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message content cannot exceed 500 characters');

        $this->service->send($message);
    }

    /** @test */
    public function it_sends_message_successfully_with_valid_content()
    {
        Http::fake([
            '*' => Http::response(['message' => 'Accepted', 'messageId' => 'test-123'], 202),
        ]);

        $message = new Message([
            'to' => '+905551111111',
            'content' => 'Test message content',
        ]);

        $result = $this->service->send($message);

        $this->assertEquals(202, $result['status']);
        $this->assertEquals('test-123', $result['body']['messageId']);
    }

    /** @test */
    public function it_handles_http_failure_gracefully()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Bad Request'], 400),
        ]);

        $message = new Message([
            'to' => '+905551111111',
            'content' => 'Test message',
        ]);

        $result = $this->service->send($message);

        $this->assertEquals(400, $result['status']);
        $this->assertEquals('Bad Request', $result['body']['error']);
    }

    /** @test */
    public function it_accepts_content_exactly_500_characters()
    {
        Http::fake([
            '*' => Http::response(['message' => 'Accepted', 'messageId' => 'test-123'], 202),
        ]);

        $message = new Message([
            'to' => '+905551111111',
            'content' => str_repeat('a', 500), // Tam 500 karakter
        ]);

        $result = $this->service->send($message);

        $this->assertEquals(202, $result['status']);
    }
}
