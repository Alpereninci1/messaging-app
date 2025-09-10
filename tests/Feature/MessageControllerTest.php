<?php

namespace Tests\Feature;

use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_sent_message_ids()
    {
        // Test verileri oluştur
        Message::create([
            'to' => '+905551111111',
            'content' => 'Message 1',
            'status' => 'sent',
            'external_message_id' => 'id-1'
        ]);
        
        Message::create([
            'to' => '+905552222222',
            'content' => 'Message 2',
            'status' => 'sent',
            'external_message_id' => 'id-2'
        ]);
        
        Message::create([
            'to' => '+905553333333',
            'content' => 'Message 3',
            'status' => 'pending'
        ]);

        $response = $this->getJson('/api/messages/sent');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data'
                ])
                ->assertJsonCount(2, 'data')
                ->assertJson([
                    'data' => ['id-1', 'id-2']
                ]);
    }

    /** @test */
    public function it_returns_empty_array_when_no_sent_messages()
    {
        Message::create([
            'to' => '+905551111111',
            'content' => 'Message 1',
            'status' => 'pending'
        ]);

        $response = $this->getJson('/api/messages/sent');

        $response->assertStatus(200)
                ->assertJson([
                    'data' => []
                ]);
    }

    /** @test */
    public function it_excludes_messages_without_external_id()
    {
        Message::create([
            'to' => '+905551111111',
            'content' => 'Message 1',
            'status' => 'sent',
            'external_message_id' => 'id-1'
        ]);
        
        Message::create([
            'to' => '+905552222222',
            'content' => 'Message 2',
            'status' => 'sent',
            'external_message_id' => null
        ]);

        $response = $this->getJson('/api/messages/sent');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJson([
                    'data' => ['id-1']
                ]);
    }
}
