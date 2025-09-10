<?php

namespace App\Jobs;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\MessageSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Message $message)
    {
        $this->onQueue('messages');
    }

    public function handle(MessageRepositoryInterface $repo, MessageSenderService $service): void
    {
        if ($this->message->status !== 'pending') {
            return;
        }

        try {
            $result = $service->send($this->message);
            $status = $result['status'] ?? 0;
            $body   = $result['body'] ?? [];

            if (($status === 202 || $status === 200) && isset($body['messageId'])) {
                $repo->markSent($this->message, $body['messageId'], $status);
            } else {
                $repo->markFailed($this->message, $status);
                $this->release(10); // retry later
            }
        } catch (\Exception $e) {
            $repo->markFailed($this->message, 0, $e->getMessage());
            $this->release(10); // retry later
        }
    }

    public function failed(Throwable $exception): void
    {
        // Optionally log or notify
    }
}
