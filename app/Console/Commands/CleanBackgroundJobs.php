<?php

namespace App\Console\Commands;

use App\Models\BackgroundJobRetry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanBackgroundJobs extends Command
{
    protected $signature = 'background-jobs:clean';
    protected $description = 'Clean up background jobs database and logs';

    public function handle()
    {
        // Clear all jobs from database
        BackgroundJobRetry::truncate();
        $this->info('Cleared all background jobs from database');

        // Clear log files
        $logFiles = [
            storage_path('logs/background_jobs.log'),
            storage_path('logs/background_jobs_errors.log')
        ];

        foreach ($logFiles as $logFile) {
            if (File::exists($logFile)) {
                File::put($logFile, '');
                $this->info("Cleared log file: " . basename($logFile));
            }
        }

        $this->info('Cleanup completed successfully');
    }
} 