<?php

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a background job
     *
     * @param string $class The fully qualified class name
     * @param string $method The method to call
     * @param array $params The parameters to pass to the method
     * @return bool Whether the job was started successfully
     */
    function runBackgroundJob($class, $method, $params = [])
    {
        try {
            $command = [
                'php',
                base_path('artisan'),
                'background-job:run',
                $class,
                $method,
                json_encode($params)
            ];

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                $process = new \Symfony\Component\Process\Process($command);
                $process->setOptions(['create_new_console' => true]);
            } else {
                // Unix-based systems
                $command = array_merge(['nohup'], $command, ['>', '/dev/null', '2>&1', '&']);
                $process = new \Symfony\Component\Process\Process($command);
            }

            $process->start();
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::channel('background_jobs_errors')->error('Failed to start background job', [
                'class' => $class,
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 