<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BackgroundJobRetry;

class BackgroundJobController extends Controller
{
    public function index()
    {
        $retries = BackgroundJobRetry::orderBy('created_at', 'desc')->paginate(10);
        
        $stats = [
            'total' => BackgroundJobRetry::count(),
            'completed' => BackgroundJobRetry::where('status', 'completed')->count(),
            'failed' => BackgroundJobRetry::where('status', 'failed')->count(),
            'running' => BackgroundJobRetry::where('status', 'running')->count(),
            'cancelled' => BackgroundJobRetry::where('status', 'cancelled')->count(),
        ];

        return view('index', compact('retries', 'stats'));
    }

    public function show($id)
    {
        $retry = BackgroundJobRetry::findOrFail($id);
        return view('show', compact('retry'));
    }

    public function logs()
    {
        $mainLogPath = storage_path('logs/background_jobs.log');
        $errorLogPath = storage_path('logs/background_jobs_errors.log');

        $mainLog = file_exists($mainLogPath) ? file_get_contents($mainLogPath) : 'No logs found';
        $errorLog = file_exists($errorLogPath) ? file_get_contents($errorLogPath) : 'No error logs found';

        return view('logs', [
            'mainLog' => $this->formatLog($mainLog),
            'errorLog' => $this->formatLog($errorLog)
        ]);
    }

    public function retry($id)
    {
        $retry = BackgroundJobRetry::findOrFail($id);
        
        if ($retry->attempt >= $retry->max_attempts) {
            $retry->update([
                'status' => 'failed',
                'error' => 'Maximum number of attempts reached'
            ]);
            return redirect('/');
        }

        // Execute the retry command
        $command = sprintf(
            'php artisan retry %d > /dev/null 2>&1 &',
            $retry->id
        );
        exec($command);

        return redirect('/');
    }

    public function cancel($id)
    {
        $retry = BackgroundJobRetry::findOrFail($id);
        
        if ($retry->status === 'running') {
            $retry->update([
                'status' => 'cancelled',
                'error' => 'Job was cancelled by user'
            ]);
        }

        return redirect('/');
    }

    private function formatLog($log)
    {
        $lines = explode("\n", $log);
        $formattedLines = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Check if line contains JSON
            if (preg_match('/\{.*\}/', $line, $matches)) {
                $json = json_decode($matches[0], true);
                if ($json) {
                    $line = str_replace($matches[0], json_encode($json, JSON_PRETTY_PRINT), $line);
                }
            }

            $formattedLines[] = $line;
        }

        return implode("\n", $formattedLines);
    }
} 