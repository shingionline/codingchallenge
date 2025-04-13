<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class TestFailedJob
{
    public function process()
    {
        Log::channel('background_jobs')->info("TestFailedJob started");
        Log::channel('background_jobs')->error("TestFailedJob failed");
        throw new \Exception("TestFailedJob failed");
    }
} 