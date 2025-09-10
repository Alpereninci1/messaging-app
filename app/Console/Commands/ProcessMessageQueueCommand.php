<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessMessageQueueCommand extends Command
{
    protected $signature = 'messages:process-queue {--timeout=60} {--tries=3}';
    protected $description = 'Process message queue manually (alternative to queue:work).';

    public function handle(): int
    {
        $timeout = (int) $this->option('timeout');
        $tries = (int) $this->option('tries');
        
        $this->info("Starting message queue processor...");
        $this->info("Timeout: {$timeout}s, Max tries: {$tries}");

        // Queue worker'ı başlat
        $this->call('queue:work', [
            'connection' => 'sync',
            '--queue' => 'messages',
            '--timeout' => $timeout,
            '--tries' => $tries,
            '--verbose' => true
        ]);

        return self::SUCCESS;
    }
}
