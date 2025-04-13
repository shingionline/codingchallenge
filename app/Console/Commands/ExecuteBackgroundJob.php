<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExecuteBackgroundJob extends Command
{
    protected $signature = 'background-job:execute {class} {method} {params}';
    protected $description = 'Execute a background job';

    public function handle()
    {
        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = json_decode($this->argument('params'), true);

        try {
            $instance = app($class);
            $result = call_user_func_array([$instance, $method], $params);

            $this->logJobCompletion($class, $method, $params, $result);
            return 0;

        } catch (\Exception $e) {
            $this->handleJobFailure($class, $method, $params, $e);
            return 1;
        }
    }

    protected function logJobCompletion($class, $method, $params, $result)
    {
        Log::channel('background_jobs')->info('Job completed', [
            'timestamp' => now(),
            'class' => $class,
            'method' => $method,
            'params' => $params,
            'result' => $result,
            'status' => 'completed'
        ]);
    }

    protected function handleJobFailure($class, $method, $params, $exception)
    {
        $attempt = $this->getRetryAttempt($class, $method);
        $maxAttempts = config('background-jobs.retry.max_attempts');

        if ($attempt < $maxAttempts) {
            $this->scheduleRetry($class, $method, $params, $attempt);
        }

        $this->logError($class, $method, $params, $exception, $attempt);
    }

    protected function getRetryAttempt($class, $method)
    {
        $key = "background_job:{$class}:{$method}:attempt";
        return cache()->increment($key);
    }

    protected function scheduleRetry($class, $method, $params, $attempt)
    {
        $delay = config('background-jobs.retry.delay_seconds') * $attempt;
        
        // Schedule the retry using Laravel's scheduler
        // This is a simplified version - in a real implementation, you'd want to use a proper job scheduler
        sleep($delay);
        
        $this->call('background-job:run', [
            'class' => $class,
            'method' => $method,
            'params' => json_encode($params)
        ]);
    }

    protected function logError($class, $method, $params, $exception, $attempt)
    {
        Log::channel('background_jobs_errors')->error('Job failed', [
            'timestamp' => now(),
            'class' => $class,
            'method' => $method,
            'params' => $params,
            'error' => $exception->getMessage(),
            'attempt' => $attempt,
            'trace' => $exception->getTraceAsString()
        ]);
    }
} 