<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ExecuteBackgroundJob::class,
        Commands\ProcessBackgroundJobRetries::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Process retries every minute
        $schedule->command('background-job:process-retries')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
} 