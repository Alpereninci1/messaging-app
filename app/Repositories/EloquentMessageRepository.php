<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function getPending(int $limit = 2): Collection
    {
        return Message::query()
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function markSent(Message $message, string $externalId, int $code): void
    {
        $message->forceFill([
            'status' => 'sent',
            'external_message_id' => $externalId,
            'response_code' => $code,
            'sent_at' => now(),
        ])->save();
    }

    public function markFailed(Message $message, ?int $code = null, ?string $reason = null): void
    {
        $message->forceFill([
            'status' => 'failed',
            'response_code' => $code,
        ])->save();
    }

    public function listSentExternalIds(): Collection
    {
        return Message::query()
            ->where('status', 'sent')
            ->whereNotNull('external_message_id')
            ->pluck('external_message_id');
    }
}
