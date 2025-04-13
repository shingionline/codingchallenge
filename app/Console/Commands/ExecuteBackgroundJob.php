<?php

namespace App\Console\Commands;

use App\Models\BackgroundJobRetry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExecuteBackgroundJob extends Command
{
    protected $signature = 'background-job:execute {class} {method} {params} {--retry-id=}';
    protected $description = 'Execute a background job';

    public function handle()
    {
        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = json_decode($this->argument('params'), true);
        $retryId = $this->option('retry-id');

        try {
            if ($retryId) {
                $retry = BackgroundJobRetry::findOrFail($retryId);
                $retry->markAsRunning();
            }

            Log::channel('background_jobs')->info('Job execution attempt', [
                'class' => $class,
                'method' => $method,
                'params' => $params,
                'retry_id' => $retryId
            ]);

            $instance = app($class);
            $result = $instance->$method(...$params);

            if ($retryId) {
                $retry->markAsCompleted();
            }

            Log::channel('background_jobs')->info('Job completed successfully', [
                'class' => $class,
                'method' => $method,
                'result' => $result,
                'retry_id' => $retryId
            ]);

            return 0;
        } catch (\Exception $e) {
            Log::channel('background_jobs_errors')->error('Job execution failed', [
                'class' => $class,
                'method' => $method,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'retry_id' => $retryId
            ]);

            if ($retryId) {
                $retry = BackgroundJobRetry::find($retryId);
                if ($retry && $retry->attempt < $retry->max_attempts) {
                    $retry->scheduleNextAttempt();
                    Log::channel('background_jobs')->info('Job retry scheduled', [
                        'retry_id' => $retryId,
                        'next_attempt' => $retry->attempt + 1,
                        'next_attempt_at' => $retry->next_attempt_at
                    ]);
                } else {
                    $retry->markAsFailed($e->getMessage());
                }
            } else {
                // Create new retry record for first failure
                $retryConfig = config('background-jobs.retry');
                BackgroundJobRetry::create([
                    'class' => $class,
                    'method' => $method,
                    'params' => $params,
                    'attempt' => 1,
                    'max_attempts' => $retryConfig['max_attempts'],
                    'delay_seconds' => $retryConfig['delay_seconds'],
                    'next_attempt_at' => now()->addSeconds($retryConfig['delay_seconds']),
                    'status' => 'pending',
                    'error' => $e->getMessage()
                ]);
            }

            return 1;
        }
    }
} 