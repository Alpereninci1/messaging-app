<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Console\Command;

class DispatchMessagesCommand extends Command
{
    protected $signature = 'messages:dispatch {--per-batch=2} {--interval=5} {--max-batches=0}';
    protected $description = 'Dispatch pending messages to the queue in controlled batches.';

    public function handle(MessageRepositoryInterface $repo): int
    {
        $perBatch = (int) $this->option('per-batch');
        $interval = (int) $this->option('interval');
        $maxBatches = (int) $this->option('max-batches');
        $batches = 0;

        $this->info("Starting dispatcher: {$perBatch} messages every {$interval}s");

        while (true) {
            $pending = $repo->getPending($perBatch);

            if ($pending->isEmpty()) {
                $this->info('No pending messages. Sleeping...');
            } else {
                foreach ($pending as $message) {
                    SendMessageJob::dispatchSync($message);
                    $this->line("Sent message #{$message->id} -> {$message->to}");
                }
                $batches++;
            }

            if ($maxBatches > 0 && $batches >= $maxBatches) {
                $this->info('Reached max-batches. Exiting.');
                break;
            }

            sleep($interval);
        }

        return self::SUCCESS;
    }
}
