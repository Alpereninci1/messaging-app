<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MessageRepositoryInterface
{
    /** @return Collection<int, Message> */
    public function getPending(int $limit = 2): \Illuminate\Support\Collection;

    public function markSent(Message $message, string $externalId, int $code): void;

    public function markFailed(Message $message, ?int $code = null, ?string $reason = null): void;

    /** @return Collection<int, string> */
    public function listSentExternalIds(): \Illuminate\Support\Collection;
}
