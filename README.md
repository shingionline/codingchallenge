# Background Job System

A Laravel-based background job system that allows you to execute long-running tasks asynchronously with retry capabilities.

## Features

- Execute background jobs asynchronously
- Automatic retry mechanism for failed jobs
- Job status tracking (running, completed, failed, cancelled)
- Web interface for job management
- Detailed job logs
- Job cancellation support
- Manual retry capability

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env.example` to `.env` and configure your database
4. Run migrations:
   ```bash
   php artisan migrate
   ```

## Usage

### Creating a Background Job

1. Create a new job class that extends `BackgroundJob`:
   ```php
   use App\Jobs\BackgroundJob;

   class MyJob extends BackgroundJob
   {
       public function handle()
       {
           // Your job logic here
       }
   }
   ```

2. Execute the job:
   ```php
   $job = new MyJob();
   $job->dispatch();
   ```

### Job Statuses

- **Running**: Job is currently being executed
- **Completed**: Job finished successfully
- **Failed**: Job failed after reaching maximum attempts
- **Cancelled**: Job was manually cancelled by user

### Web Interface

Access the web interface at `/` to:
- View all jobs
- See job statistics
- Retry failed jobs
- Cancel running jobs
- View detailed job logs

### Job Management

#### Retrying Jobs

1. Through the web interface:
   - Click the "Retry" button on a failed job
   - The job will be executed immediately

2. Through the command line:
   ```bash
   php artisan retry {job_id}
   ```

#### Cancelling Jobs

1. Through the web interface:
   - Click the "Cancel" button on a running job
   - The job will be marked as cancelled

### Logs

View detailed logs at `/view/logs`:
- Main job execution logs
- Error logs for failed jobs

### Configuration

The system can be configured through environment variables:

```env
BACKGROUND_JOB_MAX_ATTEMPTS=3
BACKGROUND_JOB_RETRY_DELAY=60
```

- `BACKGROUND_JOB_MAX_ATTEMPTS`: Maximum number of retry attempts (default: 3)
- `BACKGROUND_JOB_RETRY_DELAY`: Delay between retries in seconds (default: 60)

## Architecture

### Components

1. **BackgroundJob**: Base class for all background jobs
2. **BackgroundJobRetry**: Model for tracking job attempts
3. **BackgroundJobController**: Web interface controller
4. **ExecuteBackgroundJob**: Artisan command for job execution
5. **RetryJob**: Artisan command for manual job retry

### Flow

1. Job is created and dispatched
2. System creates a retry record
3. Job is executed asynchronously
4. If job fails:
   - System checks attempt count
   - If under max attempts, schedules retry
   - If max attempts reached, marks as failed
5. User can manually retry or cancel jobs

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.
