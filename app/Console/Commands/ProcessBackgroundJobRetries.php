<?php

namespace App\Console\Commands;

use App\Models\BackgroundJobRetry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessBackgroundJobRetries extends Command
{
    protected $signature = 'background-job:process-retries';
    protected $description = 'Process pending background job retries';

    public function handle()
    {
        $retries = BackgroundJobRetry::where('status', 'pending')
            ->where('next_attempt_at', '<=', now())
            ->get();

        foreach ($retries as $retry) {
            try {
                $this->info("Processing retry for {$retry->class}::{$retry->method} (Attempt {$retry->attempt})");
                
                $this->call('background-job:execute', [
                    'class' => $retry->class,
                    'method' => $retry->method,
                    'params' => json_encode($retry->params),
                    '--retry-id' => $retry->id
                ]);
            } catch (\Exception $e) {
                Log::channel('background_jobs_errors')->error('Failed to process retry', [
                    'retry_id' => $retry->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
} 