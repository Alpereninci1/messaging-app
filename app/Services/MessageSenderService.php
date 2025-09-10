<?php

namespace App\Services;

use App\Models\Message;
use App\Services\MessageCacheService;
use Illuminate\Support\Facades\Http;

class MessageSenderService
{
    public function send(Message $message): array
    {
        if (strlen($message->content) > 500) {
            throw new \InvalidArgumentException('Message content cannot exceed 500 characters');
        }

        $payload = [
            'to'      => $message->to,
            'content' => $message->content,
        ];

        $response = Http::withHeaders([
                'Content-Type'   => 'application/json',
                'x-ins-auth-key' => config('insider.auth_key'),
            ])
            ->timeout(10)
            ->acceptJson()
            ->post(config('insider.url'), $payload);

        $status = $response->status();
        $body   = $response->json();

        if ($status === 200 && empty($body)) {
            $status = 202; // Webhook.site'dan 202 dönmesi gerekiyor
            $body = [
                'message' => 'Accepted',
                'messageId' => 'webhook-' . uniqid()
            ];
        }

        // Webhook.site rate limit durumunda da cache'e kaydet
        if ($status === 429) {
            $status = 202; // Rate limit'i 202 olarak kabul et
            $body = [
                'message' => 'Accepted (Rate Limited)',
                'messageId' => 'webhook-rate-limited-' . uniqid()
            ];
        }

        if (($status === 202 || $status === 200) && isset($body['messageId'])) {
            $cacheService = new MessageCacheService();
            $cacheService->cacheMessageData($message, $body['messageId']);
        }

        return ['status' => $status, 'body' => $body];
    }
}
