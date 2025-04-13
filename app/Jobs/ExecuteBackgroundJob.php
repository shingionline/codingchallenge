<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\Models\BackgroundJob;

class ExecuteBackgroundJob
{
    protected $class;
    protected $method;
    protected $params;

    public function __construct($class, $method, $params = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->params = $params;
    }

    public function handle()
    {
        $job = BackgroundJob::create([
            'class' => $this->class,
            'method' => $this->method,
            'params' => $this->params,
            'status' => 'running',
            'started_at' => now()
        ]);

        try {
            Log::channel('background_jobs')->info('Job started', [
                'job_id' => $job->id,
                'class' => $this->class,
                'method' => $this->method,
                'params' => $this->params
            ]);

            $instance = app($this->class);
            $result = call_user_func_array([$instance, $this->method], $this->params);

            $job->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            Log::channel('background_jobs')->info('Job completed', [
                'job_id' => $job->id,
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            $job->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error' => $e->getMessage()
            ]);

            Log::channel('background_jobs_errors')->error('Job failed', [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
} 