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
- Web-based dashboard for job management
- Job queue management with status tracking

## Installation

1. Install dependencies
   ```bash
   git clone https://github.com/shingionline/codingchallenge.git
   cd codingchallenge
   composer install
   ```
2. Run migrations to create the retry tracking table
   ```bash
   php artisan migrate
   ```
   > **Note**: This step is required for the retry mechanism to work. It creates the `background_job_retries` table that tracks failed jobs and their retry attempts.

3. Make the script executable (Unix-based systems)
   ```bash
   chmod +x run-job.php
   ```
4. Set up the scheduler in your crontab
   ```bash
   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
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

### Web Dashboard

Start the development server:
```bash
php artisan serve
```

Access the web dashboard at http://127.0.0.1:8000 to:
- View all jobs and their statuses
- Monitor job statistics
- Retry failed jobs
- Cancel running jobs
- View detailed job logs

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

The system includes a robust retry mechanism that automatically handles failed jobs:

1. When a job fails:
   - A retry record is created in the database
   - The next attempt is scheduled based on the delay configuration
   - Status is set to 'pending'

2. The scheduler (running every minute):
   - Checks for pending retries that are due
   - Executes each retry in a separate process
   - Updates the retry status accordingly

3. Job Statuses:
   - `pending`: Waiting to be retried
   - `running`: Currently being executed
   - `completed`: Successfully completed
   - `failed`: Failed after all attempts
   - `cancelled`: Manually cancelled by user

4. Monitoring:
   - View job statuses and statistics in the web dashboard
   - Check the `background_job_retries` table in the database
   - Review logs in `storage/logs/`:
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

### Error Handling

- All exceptions are caught and logged
- Detailed error information is stored
- Failed jobs are automatically retried
- Comprehensive error tracking in database

## Limitations

- No job prioritization
