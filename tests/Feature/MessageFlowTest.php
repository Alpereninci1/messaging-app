<?php

namespace Tests\Feature;

use App\Jobs\SendMessageJob;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class MessageFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Redis'i mock'la
        Redis::shouldReceive('set')->andReturn(true);
        Redis::shouldReceive('get')->andReturn(json_encode([
            'external_message_id' => 'abc-123',
            'sent_at' => now()->toISOString()
        ]));
    }

    /** @test */
    public function it_marks_message_sent_on_202_with_message_id()
    {
        Http::fake([
            '*' => Http::response(['message' => 'Accepted', 'messageId' => 'abc-123'], 202),
        ]);

        $msg = Message::create([
            'to' => '+905551111111',
            'content' => 'Insider - Project',
        ]);

        SendMessageJob::dispatchSync($msg->fresh());

        $this->assertDatabaseHas('messages', [
            'id' => $msg->id,
            'status' => 'sent',
            'external_message_id' => 'abc-123',
        ]);
    }

    /** @test */
    public function it_marks_message_failed_on_non_202_response()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Bad Request'], 400),
        ]);

        $msg = Message::create([
            'to' => '+905551111111',
            'content' => 'Test message',
        ]);

        SendMessageJob::dispatchSync($msg->fresh());

        $this->assertDatabaseHas('messages', [
            'id' => $msg->id,
            'status' => 'failed',
            'response_code' => 400,
        ]);
    }

    /** @test */
    public function it_caches_message_data_in_redis_on_success()
    {
        Http::fake([
            '*' => Http::response(['message' => 'Accepted', 'messageId' => 'abc-123'], 202),
        ]);

        $msg = Message::create([
            'to' => '+905551111111',
            'content' => 'Test message',
        ]);

        SendMessageJob::dispatchSync($msg->fresh());

        $cacheKey = 'message:' . $msg->id;
        $cachedData = Redis::get($cacheKey);
        
        $this->assertNotNull($cachedData);
        
        $decodedData = json_decode($cachedData, true);
        $this->assertEquals('abc-123', $decodedData['external_message_id']);
        $this->assertArrayHasKey('sent_at', $decodedData);
    }

    /** @test */
    public function it_handles_http_timeout_gracefully()
    {
        Http::fake([
            '*' => Http::response([], 408), // Timeout
        ]);

        $msg = Message::create([
            'to' => '+905551111111',
            'content' => 'Test message',
        ]);

        SendMessageJob::dispatchSync($msg->fresh());

        $this->assertDatabaseHas('messages', [
            'id' => $msg->id,
            'status' => 'failed',
            'response_code' => 408,
        ]);
    }

    /** @test */
    public function it_does_not_send_duplicate_messages()
    {
        Http::fake([
            '*' => Http::response(['message' => 'Accepted', 'messageId' => 'abc-123'], 202),
        ]);

        $msg = Message::create([
            'to' => '+905551111111',
            'content' => 'Test message',
            'status' => 'sent', // Already sent
        ]);

        SendMessageJob::dispatchSync($msg->fresh());

        // Should not make HTTP request for already sent message
        Http::assertSentCount(0);
    }
}
