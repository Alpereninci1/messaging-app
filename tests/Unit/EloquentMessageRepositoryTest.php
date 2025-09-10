<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Repositories\EloquentMessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentMessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentMessageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentMessageRepository();
    }

    /** @test */
    public function it_returns_pending_messages_in_correct_order()
    {
        // Test verileri oluştur
        Message::create(['to' => '+905551111111', 'content' => 'Message 1', 'status' => 'pending']);
        Message::create(['to' => '+905552222222', 'content' => 'Message 2', 'status' => 'sent']);
        Message::create(['to' => '+905553333333', 'content' => 'Message 3', 'status' => 'pending']);

        $pending = $this->repository->getPending();

        $this->assertCount(2, $pending);
        $this->assertEquals('Message 1', $pending->first()->content);
        $this->assertEquals('Message 3', $pending->last()->content);
    }

    /** @test */
    public function it_respects_limit_parameter()
    {
        // 5 pending mesaj oluştur
        for ($i = 1; $i <= 5; $i++) {
            Message::create(['to' => "+90555111111{$i}", 'content' => "Message {$i}", 'status' => 'pending']);
        }

        $pending = $this->repository->getPending(3);

        $this->assertCount(3, $pending);
    }

    /** @test */
    public function it_marks_message_as_sent_with_external_id()
    {
        $message = Message::create(['to' => '+905551111111', 'content' => 'Test message', 'status' => 'pending']);

        $this->repository->markSent($message, 'external-123', 202);

        $message->refresh();
        $this->assertEquals('sent', $message->status);
        $this->assertEquals('external-123', $message->external_message_id);
        $this->assertEquals(202, $message->response_code);
        $this->assertNotNull($message->sent_at);
    }

    /** @test */
    public function it_marks_message_as_failed()
    {
        $message = Message::create(['to' => '+905551111111', 'content' => 'Test message', 'status' => 'pending']);

        $this->repository->markFailed($message, 400);

        $message->refresh();
        $this->assertEquals('failed', $message->status);
        $this->assertEquals(400, $message->response_code);
    }

    /** @test */
    public function it_returns_sent_external_ids()
    {
        Message::create(['to' => '+905551111111', 'content' => 'Message 1', 'status' => 'sent', 'external_message_id' => 'id-1']);
        Message::create(['to' => '+905552222222', 'content' => 'Message 2', 'status' => 'sent', 'external_message_id' => 'id-2']);
        Message::create(['to' => '+905553333333', 'content' => 'Message 3', 'status' => 'pending']);

        $externalIds = $this->repository->listSentExternalIds();

        $this->assertCount(2, $externalIds);
        $this->assertContains('id-1', $externalIds);
        $this->assertContains('id-2', $externalIds);
    }

    /** @test */
    public function it_excludes_messages_without_external_id()
    {
        Message::create(['to' => '+905551111111', 'content' => 'Message 1', 'status' => 'sent', 'external_message_id' => 'id-1']);
        Message::create(['to' => '+905552222222', 'content' => 'Message 2', 'status' => 'sent', 'external_message_id' => null]);

        $externalIds = $this->repository->listSentExternalIds();

        $this->assertCount(1, $externalIds);
        $this->assertContains('id-1', $externalIds);
    }
}
