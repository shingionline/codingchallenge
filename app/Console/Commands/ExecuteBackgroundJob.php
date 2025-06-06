<?php

namespace App\Console\Commands;

use App\Models\BackgroundJobRetry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExecuteBackgroundJob extends Command
{
    protected $signature = 'background-job:execute {class} {method} {params} {retry_id?}';
    protected $description = 'Execute a background job';

    public function handle()
    {
        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = json_decode($this->argument('params'), true) ?? [];
        $retryId = $this->argument('retry_id');

        try {
            // If this is a retry, find the existing record
            if ($retryId) {
                $retry = BackgroundJobRetry::findOrFail($retryId);
                
                // Check if we've reached max attempts
                if ($retry->attempt >= $retry->max_attempts) {
                    $retry->update([
                        'status' => 'failed',
                        'error' => 'Maximum number of attempts reached',
                        'updated_at' => now()
                    ]);
                    return 1;
                }

                $retry->update([
                    'status' => 'running',
                    'attempt' => $retry->attempt + 1,
                    'updated_at' => now()
                ]);
            } else {
                // Create initial job record
                $retryConfig = config('background_jobs.retry');
                $retry = BackgroundJobRetry::create([
                    'class' => $class,
                    'method' => $method,
                    'params' => $params,
                    'attempt' => 1,
                    'max_attempts' => $retryConfig['max_attempts'],
                    'delay_seconds' => $retryConfig['delay_seconds'],
                    'next_attempt_at' => now(),
                    'status' => 'running'
                ]);
            }

            Log::channel('background_jobs')->info('Job execution attempt', [
                'class' => $class,
                'method' => $method,
                'params' => $params,
                'retry_id' => $retry->id,
                'attempt' => $retry->attempt,
                'max_attempts' => $retry->max_attempts
            ]);

            // Ensure params is an array
            $params = is_array($params) ? $params : [$params];
            
            $instance = app($class);
            $result = $instance->$method(...$params);

            $retry->update([
                'status' => 'completed'
            ]);

            Log::channel('background_jobs')->info('Job completed successfully', [
                'class' => $class,
                'method' => $method,
                'result' => $result,
                'retry_id' => $retry->id
            ]);

            return 0;
        } catch (\Exception $e) {
            Log::channel('background_jobs_errors')->error('Job execution failed', [
                'class' => $class,
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'retry_id' => $retry->id ?? null
            ]);

            if (isset($retry)) {
                // Check if we've reached max attempts
                if ($retry->attempt >= $retry->max_attempts) {
                    $retry->update([
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'updated_at' => now()
                    ]);
                    
                    Log::channel('background_jobs_errors')->error('Job failed permanently - max attempts reached', [
                        'retry_id' => $retry->id,
                        'attempt' => $retry->attempt,
                        'max_attempts' => $retry->max_attempts,
                        'error' => $e->getMessage()
                    ]);
                } else {
                    $retry->update([
                        'next_attempt_at' => now()->addSeconds($retry->delay_seconds),
                        'status' => 'running',
                        'error' => $e->getMessage(),
                        'updated_at' => now()
                    ]);

                    Log::channel('background_jobs')->info('Job retry scheduled', [
                        'retry_id' => $retry->id,
                        'next_attempt' => $retry->attempt + 1,
                        'next_attempt_at' => $retry->next_attempt_at
                    ]);
                }
            }

            return 1;
        }
    }
} 