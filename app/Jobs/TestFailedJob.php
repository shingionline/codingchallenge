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

    protected $attemptCount = 0;

    public function __construct()
    {
        $this->attemptCount = 0;
    }

    public function process()
    {
        $this->attemptCount++;
        
        Log::channel('background_jobs')->info("TestFailedJob attempt {$this->attemptCount} started");
        
        // Always fail, regardless of attempt number
        Log::channel('background_jobs')->error("TestFailedJob attempt {$this->attemptCount} failed");
        throw new \Exception("TestFailedJob failed on attempt {$this->attemptCount}");
    }
} 