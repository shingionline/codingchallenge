# Custom Background Job Runner

A custom background job runner system for Laravel that allows executing PHP classes as background jobs without using Laravel's built-in queue system.

## Features

- Execute PHP classes as background jobs
- Cross-platform support (Windows and Unix-based systems)
- Configurable retry mechanism
- Comprehensive logging
- Security through allowed jobs configuration
- Error handling and reporting

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the configuration file:
   ```bash
   php artisan vendor:publish --tag=config
   ```

## Configuration

Edit `config/background-jobs.php` to:

1. Define allowed jobs and their methods
2. Configure retry attempts and delays
3. Set up logging paths

Example configuration:
```php
'allowed_jobs' => [
    'App\Jobs\SampleJob' => ['allowed_methods' => ['process']],
],
'retry' => [
    'max_attempts' => 3,
    'delay_seconds' => 60,
],
```

## Usage

### Running a Background Job

Use the `runBackgroundJob` helper function:

```php
use App\Jobs\SampleJob;

// Run a job
$success = runBackgroundJob(SampleJob::class, 'process', ['data' => 'example']);

if ($success) {
    // Job started successfully
} else {
    // Failed to start job
}
```

### Creating a New Job

1. Create a new class in `app/Jobs/`
2. Add methods that should be run in the background
3. Register the class and methods in `config/background-jobs.php`

Example job:
```php
namespace App\Jobs;

class SampleJob
{
    public function process($data)
    {
        // Do some work
        return ['processed' => true];
    }
}
```

### Monitoring Jobs

Check the log files in `storage/logs/`:
- `background_jobs.log` for general job status
- `background_jobs_errors.log` for error information

## Security

The system includes several security measures:
- Only pre-approved classes and methods can be executed
- Input parameters are validated
- Comprehensive error logging

## Advanced Features

### Retry Mechanism

Failed jobs are automatically retried based on configuration:
- Maximum number of attempts
- Delay between retries
- Exponential backoff

### Error Handling

- All exceptions are caught and logged
- Detailed error information is stored
- Failed jobs can be retried automatically

## Limitations

- No built-in job queue management
- No job prioritization
- No job cancellation mechanism
- No web-based dashboard
