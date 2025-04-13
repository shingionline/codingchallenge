<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Background Jobs
    |--------------------------------------------------------------------------
    |
    | Define the classes and methods that are allowed to be executed as background jobs.
    | Format: 'ClassName' => ['allowed_methods' => ['method1', 'method2']]
    |
    */
    'allowed_jobs' => [
        App\Jobs\TestJob::class => [
            'allowed_methods' => ['process']
        ],
        'App\Jobs\SampleJob' => ['allowed_methods' => ['process']],
        'App\Jobs\TestFailedJob' => [
            'allowed_methods' => ['process'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the number of retry attempts and delay between retries.
    |
    */
    'retry' => [
        'max_attempts' => 3,
        'delay_seconds' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the logging paths for background jobs.
    |
    */
    'logging' => [
        'main_log' => 'background_jobs.log',
        'error_log' => 'background_jobs_errors.log',
    ],
]; 