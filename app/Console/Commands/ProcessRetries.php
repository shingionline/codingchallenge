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
            ->where('attempt', '<', 'max_attempts')
            ->where('next_attempt_at', '<=', now())
            ->get();

        foreach ($retries as $retry) {
            $this->info("Processing retry for job {$retry->id}");
            
            // Update job status to running and increment attempt
            $retry->update([
                'attempt' => $retry->attempt + 1,
                'next_attempt_at' => now()->addSeconds($retry->delay_seconds)
            ]);

            // Execute the job
            $class = $retry->class;
            $method = $retry->method;
            $params = json_decode($retry->params, true);

            try {
                $job = new $class();
                $job->$method(...$params);
                
                // Job succeeded
                $retry->update(['status' => 'completed']);
                $this->info("Job {$retry->id} completed successfully");
            } catch (\Exception $e) {
                // Job failed - only mark as failed if we've reached max attempts
                $status = $retry->attempt >= $retry->max_attempts ? 'failed' : 'running';
                
                $retry->update([
                    'status' => $status,
                    'error' => $e->getMessage()
                ]);
                
                if ($status === 'failed') {
                    $this->error("Job {$retry->id} failed permanently: " . $e->getMessage());
                } else {
                    $this->warn("Job {$retry->id} failed, will retry later: " . $e->getMessage());
                }
            }
        }

        $this->info("Processed {$retries->count()} retries");
    }
} 