<?php

namespace App\Console\Commands;

use App\Models\BackgroundJobRetry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryJob extends Command
{
    protected $signature = 'retry {id}';
    protected $description = 'Retry a failed background job';

    public function handle()
    {
        $id = $this->argument('id');
        $retry = BackgroundJobRetry::findOrFail($id);
        
        if ($retry->attempt >= $retry->max_attempts) {
            $retry->update([
                'status' => 'failed',
                'error' => 'Maximum number of attempts reached'
            ]);
            $this->error('Maximum number of attempts reached');
            return 1;
        }

        // Update the job status and attempt count
        $retry->update([
            'status' => 'running',
            'attempt' => $retry->attempt + 1,
            'next_attempt_at' => now()
        ]);

        try {
            $class = $retry->class;
            $method = $retry->method;
            $params = json_decode($retry->params, true);
            
            $instance = app($class);
            $instance->$method(...$params);
            
            $retry->update(['status' => 'completed']);
            $this->info('Job completed successfully');
            return 0;
        } catch (\Exception $e) {
            if ($retry->attempt >= $retry->max_attempts) {
                $retry->update([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);
                $this->error('Job failed permanently: ' . $e->getMessage());
            } else {
                $retry->update([
                    'status' => 'running',
                    'error' => $e->getMessage()
                ]);
                $this->error('Job failed, will retry: ' . $e->getMessage());
            }
            return 1;
        }
    }
} 