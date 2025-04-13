<?php

namespace App\Jobs;

class TestJob
{
    public function process(...$params)
    {
        \Illuminate\Support\Facades\Log::channel('background_jobs')->info('TestJob is processing', [
            'params' => $params,
            'param_count' => count($params),
            'time' => now()->toDateTimeString()
        ]);

        // Simulate some work
        sleep(2);

        return ['status' => 'success', 'message' => 'Test job completed', 'processed_params' => $params];
    }
} 