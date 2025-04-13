<?php

namespace App\Console\Commands;

use App\Models\BackgroundJobRetry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRetries extends Command
{
    protected $signature = 'background-job:process-retries';
    protected $description = 'Process failed jobs that need retrying';

    public function handle()
    {
        $retries = BackgroundJobRetry::where('status', 'running')
            ->where('next_attempt_at', '<=', now())
            ->get();

        foreach ($retries as $retry) {
            $this->info("Processing retry for job {$retry->id}");
            
            // Use the ExecuteBackgroundJob command to handle the retry
            $this->call('background-job:execute', [
                'class' => $retry->class,
                'method' => $retry->method,
                'params' => json_encode($retry->params),
                'retry_id' => $retry->id
            ]);
        }

        $this->info("Processed {$retries->count()} retries");
    }
} 