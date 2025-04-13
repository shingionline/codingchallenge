<?php

namespace App\Jobs;

class SampleJob
{
    public function process($data)
    {
        // Simulate some work
        sleep(2);
        
        // Return some result
        return [
            'processed' => true,
            'data' => $data,
            'timestamp' => now()
        ];
    }
} 