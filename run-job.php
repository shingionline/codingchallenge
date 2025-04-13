#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ensure log directory exists and is writable
$logDir = storage_path('logs');
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Ensure log files exist and are writable
$logFiles = [
    'background_jobs.log',
    'background_jobs_errors.log'
];

foreach ($logFiles as $logFile) {
    $logPath = $logDir . '/' . $logFile;
    if (!file_exists($logPath)) {
        touch($logPath);
        chmod($logPath, 0666);
    }
}

// Get command line arguments
$className = $argv[1] ?? null;
$method = $argv[2] ?? null;
$paramsStr = $argv[3] ?? '';

if (!$className || !$method) {
    echo "Usage: php run-job.php ClassName methodName \"param1,param2\"\n";
    exit(1);
}

try {
    // Add namespace prefix
    $class = "App\\Jobs\\{$className}";

    // Validate the job is allowed
    $allowedJobs = config('background-jobs.allowed_jobs');
    if (!isset($allowedJobs[$class]) || !in_array($method, $allowedJobs[$class]['allowed_methods'])) {
        echo "Error: Job {$className}::{$method} is not allowed.\n";
        exit(1);
    }

    // Parse parameters from comma-separated string
    $params = [];
    if (!empty($paramsStr)) {
        $params = array_map('trim', explode(',', $paramsStr));
    }

    // Log job start
    \Illuminate\Support\Facades\Log::channel('background_jobs')->info('Job started', [
        'timestamp' => now(),
        'class' => $class,
        'method' => $method,
        'params' => $params,
        'status' => 'running'
    ]);

    // Create command array
    $command = [
        'php',
        __DIR__.'/artisan',
        'background-job:execute',
        $class,
        $method,
        json_encode($params)
    ];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        $process = new \Symfony\Component\Process\Process($command);
        $process->setOptions(['create_new_console' => true]);
    } else {
        // Unix-based systems
        $command = 'nohup ' . implode(' ', $command) . ' > /dev/null 2>&1 &';
        $process = \Symfony\Component\Process\Process::fromShellCommandline($command);
    }

    $process->start();
    echo "Job {$className}::{$method} started successfully.\n";
    exit(0);

} catch (\Exception $e) {
    \Illuminate\Support\Facades\Log::channel('background_jobs_errors')->error('Job error', [
        'timestamp' => now(),
        'class' => $class ?? $className,
        'method' => $method,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 