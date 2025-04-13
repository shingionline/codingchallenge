<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunBackgroundJob extends Command
{
    protected $signature = 'background-job:run {class} {method} {params?}';
    protected $description = 'Run a background job';

    public function handle()
    {
        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params') ? json_decode($this->argument('params'), true) : [];

        // Validate the job is allowed
        if (!$this->isJobAllowed($class, $method)) {
            $this->error("Job {$class}::{$method} is not allowed.");
            return 1;
        }

        try {
            $this->logJobStart($class, $method, $params);

            // Create a new process
            $process = new Process([
                'php',
                base_path('artisan'),
                'background-job:execute',
                $class,
                $method,
                json_encode($params)
            ]);

            // Run the process in the background
            $process->start();

            $this->info("Job {$class}::{$method} started successfully.");
            return 0;

        } catch (\Exception $e) {
            $this->logError($class, $method, $e);
            $this->error("Failed to start job: " . $e->getMessage());
            return 1;
        }
    }

    protected function isJobAllowed($class, $method)
    {
        $allowedJobs = config('background-jobs.allowed_jobs');
        return isset($allowedJobs[$class]) && 
               in_array($method, $allowedJobs[$class]['allowed_methods']);
    }

    protected function logJobStart($class, $method, $params)
    {
        Log::channel('background_jobs')->info('Job started', [
            'timestamp' => now(),
            'class' => $class,
            'method' => $method,
            'params' => $params,
            'status' => 'running'
        ]);
    }

    protected function logError($class, $method, $exception)
    {
        Log::channel('background_jobs_errors')->error('Job error', [
            'timestamp' => now(),
            'class' => $class,
            'method' => $method,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
} 