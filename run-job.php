#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BackgroundJobRetry;
use Illuminate\Support\Facades\Log;

if ($argc < 3) {
    die("Usage: php run-job.php ClassName methodName \"param1,param2\"\n");
}

$className = $argv[1];
$method = $argv[2];
$paramsStr = $argv[3] ?? '';

// Add namespace prefix
$class = "App\\Jobs\\{$className}";

// Parse parameters
$params = array_map('trim', explode(',', $paramsStr));

// Create job record
$retry = BackgroundJobRetry::create([
    'class' => $class,
    'method' => $method,
    'params' => json_encode($params),
    'attempt' => 1,
    'max_attempts' => 3,
    'delay_seconds' => 5,
    'next_attempt_at' => now()->addSeconds(5),
    'status' => 'running'
]);

try {
    // Execute the job
    $job = new $class();
    $job->$method(...$params);
    
    // Job succeeded
    $retry->update(['status' => 'completed']);
    Log::channel('background_jobs')->info("Job completed successfully", [
        'class' => $class,
        'method' => $method,
        'params' => $params
    ]);
} catch (\Exception $e) {
    // Job failed - only mark as failed if we've reached max attempts
    $status = $retry->attempt >= $retry->max_attempts ? 'failed' : 'running';
    
    $retry->update([
        'status' => $status,
        'error' => $e->getMessage(),
        'next_attempt_at' => now()->addSeconds($retry->delay_seconds)
    ]);
    
    Log::channel('background_jobs_errors')->error("Job failed", [
        'class' => $class,
        'method' => $method,
        'params' => $params,
        'error' => $e->getMessage(),
        'attempt' => $retry->attempt,
        'max_attempts' => $retry->max_attempts
    ]);
    
    echo "Job failed: " . $e->getMessage() . "\n";
    if ($status === 'running') {
        echo "Job will be retried when you run 'php artisan background-job:process-retries'.\n";
        echo "Current attempt: {$retry->attempt} of {$retry->max_attempts}\n";
    } else {
        echo "Job has reached maximum attempts and will not be retried.\n";
    }
    exit(1);
} 