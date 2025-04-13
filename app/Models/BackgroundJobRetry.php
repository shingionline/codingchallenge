<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundJobRetry extends Model
{
    protected $fillable = [
        'class',
        'method',
        'params',
        'attempt',
        'max_attempts',
        'delay_seconds',
        'next_attempt_at',
        'status',
        'error'
    ];

    protected $casts = [
        'params' => 'array',
        'next_attempt_at' => 'datetime'
    ];

    public function markAsRunning()
    {
        $this->update(['status' => 'running']);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'error' => $error
        ]);
    }

    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'error' => 'Job was cancelled by user'
        ]);
    }

    public function scheduleNextAttempt()
    {
        $this->update([
            'attempt' => $this->attempt + 1,
            'next_attempt_at' => now()->addSeconds($this->delay_seconds),
            'status' => 'pending'
        ]);
    }
} 