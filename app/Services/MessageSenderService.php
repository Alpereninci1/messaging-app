<?php

namespace App\Services;

use App\Http\Responses\MessageResponse;
use App\Models\Message;
use App\Services\MessageCacheService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageSenderService
{
    public function send(Message $message): array
    {
        if (strlen($message->content) > Response::HTTP_INTERNAL_SERVER_ERROR) {
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

        if ($status === Response::HTTP_OK && empty($body)) {
            $status = Response::HTTP_ACCEPTED;
            $body = [
                'message' => 'Accepted',
                'messageId' => 'webhook-' . uniqid()
            ];
        }

        if ($status === Response::HTTP_TOO_MANY_REQUESTS) {
            $body = [
                'message' => 'Accepted (Rate Limited)',
                'messageId' => 'webhook-rate-limited-' . uniqid()
            ];
            Log::info('Rate limited');
        }

        if (($status === Response::HTTP_ACCEPTED || $status === Response::HTTP_OK) && isset($body['messageId'])) {
            $cacheService = new MessageCacheService();
            $cacheService->cacheMessageData($message, $body['messageId']);
        }

        return MessageResponse::sendResponse($status, $body);
    }
}
