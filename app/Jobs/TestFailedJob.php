<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestFailedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function process()
    {
        Log::channel('background_jobs')->info("TestFailedJob started");
        Log::channel('background_jobs')->error("TestFailedJob failed");
        throw new \Exception("TestFailedJob failed");
    }
} 