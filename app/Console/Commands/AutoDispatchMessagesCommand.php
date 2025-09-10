<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Console\Command;

class AutoDispatchMessagesCommand extends Command
{
    protected $signature = 'messages:auto-dispatch {--per-batch=2} {--interval=5}';
    protected $description = 'Automatically dispatch all pending messages in batches.';

    public function handle(MessageRepositoryInterface $repo): int
    {
        $perBatch = (int) $this->option('per-batch');
        $interval = (int) $this->option('interval');
        
        $this->info("Auto-dispatching pending messages: {$perBatch} messages every {$interval}s");

        $totalProcessed = 0;
        
        while (true) {
            $pending = $repo->getPending($perBatch);

            if ($pending->isEmpty()) {
                $this->info("No more pending messages. Total processed: {$totalProcessed}");
                break;
            }

            foreach ($pending as $message) {
                SendMessageJob::dispatchSync($message);
                $this->line("Sent message #{$message->id} -> {$message->to}");
                $totalProcessed++;
            }

            if ($pending->count() < $perBatch) {
                $this->info("All pending messages processed. Total: {$totalProcessed}");
                break;
            }

            $this->info("Waiting {$interval}s before next batch...");
            sleep($interval);
        }

        return self::SUCCESS;
    }
}
