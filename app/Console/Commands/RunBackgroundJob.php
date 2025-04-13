<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExecuteBackgroundJob;

class RunBackgroundJob extends Command
{
    protected $signature = 'background-job:run {class} {method} {params?}';
    protected $description = 'Run a background job';

    public function handle()
    {
        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params') ? json_decode($this->argument('params'), true) : [];

        Log::info('Running background job', [
            'class' => $class,
            'method' => $method,
            'params' => $params
        ]);

        if (!class_exists($class)) {
            $this->error("Class {$class} does not exist");
            return 1;
        }

        if (!method_exists($class, $method)) {
            $this->error("Method {$method} does not exist in class {$class}");
            return 1;
        }

        try {
            // Dispatch the job to the queue
            ExecuteBackgroundJob::dispatch($class, $method, $params);
            
            Log::info('Job dispatched successfully', [
                'class' => $class,
                'method' => $method
            ]);

            $this->info("Job dispatched successfully");
            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to dispatch job', [
                'class' => $class,
                'method' => $method,
                'error' => $e->getMessage()
            ]);

            $this->error("Failed to dispatch job: " . $e->getMessage());
            return 1;
        }
    }
} 