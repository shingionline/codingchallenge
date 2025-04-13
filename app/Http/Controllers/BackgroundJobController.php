<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BackgroundJobRetry;

class BackgroundJobController extends Controller
{
    public function index()
    {
        $retries = BackgroundJobRetry::orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => BackgroundJobRetry::count(),
            'running' => BackgroundJobRetry::where('status', 'running')->count(),
            'completed' => BackgroundJobRetry::where('status', 'completed')->count(),
            'failed' => BackgroundJobRetry::where('status', 'failed')->count(),
        ];

        return view('background_jobs.index', compact('retries', 'stats'));
    }

    public function show($id)
    {
        $retry = BackgroundJobRetry::findOrFail($id);
        return view('background_jobs.show', compact('retry'));
    }

    public function logs()
    {
        $mainLogPath = storage_path('logs/background_jobs.log');
        $errorLogPath = storage_path('logs/background_jobs_errors.log');

        $mainLog = file_exists($mainLogPath) ? file_get_contents($mainLogPath) : 'No logs available';
        $errorLog = file_exists($errorLogPath) ? file_get_contents($errorLogPath) : 'No error logs available';

        // Format the logs for better readability
        $mainLog = $this->formatLog($mainLog);
        $errorLog = $this->formatLog($errorLog);

        return view('background_jobs.logs', compact('mainLog', 'errorLog'));
    }

    public function retry(BackgroundJobRetry $retry)
    {
        if ($retry->status === 'failed' && $retry->attempt < $retry->max_attempts) {
            $retry->update([
                'attempt' => $retry->attempt + 1,
                'next_attempt_at' => now(),
                'status' => 'pending'
            ]);

            return redirect()->back()->with('success', 'Job has been scheduled for retry');
        }

        return redirect()->back()->with('error', 'Job cannot be retried');
    }

    public function cancel(BackgroundJobRetry $retry)
    {
        if ($retry->status === 'running') {
            $retry->markAsCancelled();
            return redirect()->back()->with('success', 'Job has been cancelled');
        }

        return redirect()->back()->with('error', 'Only running jobs can be cancelled');
    }

    private function formatLog($log)
    {
        $lines = explode("\n", $log);
        $formattedLines = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Check if the line contains JSON
            if (preg_match('/\{.*\}/', $line, $matches)) {
                try {
                    $json = json_decode($matches[0], true);
                    if ($json !== null) {
                        $formattedLines[] = json_encode($json, JSON_PRETTY_PRINT);
                        continue;
                    }
                } catch (\Exception $e) {
                    // Not valid JSON, continue with normal formatting
                }
            }

            $formattedLines[] = $line;
        }

        return implode("\n", $formattedLines);
    }
} 