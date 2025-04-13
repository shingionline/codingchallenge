<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ExecuteBackgroundJob;
use App\Console\Commands\ProcessBackgroundJobRetries;
use App\Console\Commands\RunBackgroundJob;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ExecuteBackgroundJob::class,
        ProcessBackgroundJobRetries::class,
        RunBackgroundJob::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Process retries every minute
        $schedule->command('background-job:process-retries')
            ->everyMinute()
            ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
} 