# Background Job System

A custom background job runner system for Laravel that allows executing PHP classes as background jobs without using Laravel's built-in queue system.

## Features

- Execute PHP classes as background jobs
- Cross-platform support (Windows and Unix-based systems)
- Configurable retry mechanism with database tracking
- Comprehensive logging
- Security through allowed jobs configuration
- Error handling and reporting
- Command-line interface
- Automatic retry processing via scheduler

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
4. Run migrations to create the retry tracking table:
   ```bash
   php artisan migrate
   ```
   > **Note**: This step is required for the retry mechanism to work. It creates the `background_job_retries` table that tracks failed jobs and their retry attempts.

5. Make the script executable (Unix-based systems):
   ```bash
   chmod +x run-job.php
   ```
6. Set up the scheduler in your crontab:
   ```bash
   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
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

#### Using the Helper Function

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

#### Using the Command Line

```bash
# Basic usage with a single parameter
php run-job.php SampleJob process "param1"

# With multiple parameters
php run-job.php SampleJob process "param1,param2,param3"

# On Unix-based systems (if made executable)
./run-job.php SampleJob process "param1,param2"
```

Note: The script automatically adds the `App\Jobs\` namespace prefix to the class name.

### Retry Mechanism

The system includes a robust retry mechanism that:

1. Automatically tracks failed jobs in the database
2. Retries failed jobs based on configuration
3. Provides detailed status tracking
4. Survives server restarts

#### How Retries Work

1. When a job fails:
   - A retry record is created in the database
   - The next attempt is scheduled based on the delay configuration
   - Status is set to 'pending'

2. The scheduler (running every minute):
   - Checks for pending retries that are due
   - Executes each retry in a separate process
   - Updates the retry status accordingly

3. Retry Statuses:
   - `pending`: Waiting to be retried
   - `running`: Currently being executed
   - `completed`: Successfully completed
   - `failed`: Failed after all attempts

#### Monitoring Retries

You can monitor retries through:
1. The database `background_job_retries` table
2. Log files in `storage/logs/`:
   - `background_jobs.log` for general job status
   - `background_jobs_errors.log` for error information

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

## Security

The system includes several security measures:
- Only pre-approved classes and methods can be executed
- Input parameters are validated
- Comprehensive error logging
- Database-backed retry tracking

## Advanced Features

### Retry Configuration

Failed jobs are automatically retried based on configuration:
- Maximum number of attempts
- Delay between retries
- Status tracking in database
- Automatic processing via scheduler

### Error Handling

- All exceptions are caught and logged
- Detailed error information is stored
- Failed jobs are automatically retried
- Comprehensive error tracking in database

## Limitations

- No built-in job queue management
- No job prioritization
- No job cancellation mechanism
- No web-based dashboard

## Contributing

Feel free to submit issues and enhancement requests.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
