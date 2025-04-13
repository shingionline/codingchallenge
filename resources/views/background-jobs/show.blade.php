@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Job Details</div>

                <div class="card-body">
                    <div class="mb-3">
                        <strong>Class:</strong> {{ $retry->class }}
                    </div>
                    <div class="mb-3">
                        <strong>Method:</strong> {{ $retry->method }}
                    </div>
                    <div class="mb-3">
                        <strong>Parameters:</strong>
                        <pre>{{ json_encode(json_decode($retry->params), JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> 
                        <span class="badge bg-{{ $retry->status === 'completed' ? 'success' : ($retry->status === 'failed' ? 'danger' : ($retry->status === 'running' ? 'primary' : 'warning')) }}">
                            {{ ucfirst($retry->status) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Attempt:</strong> {{ $retry->attempt }} of {{ $retry->max_attempts }}
                    </div>
                    @if($retry->error)
                        <div class="mb-3">
                            <strong>Error:</strong>
                            <pre class="text-danger">{{ $retry->error }}</pre>
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>Created At:</strong> {{ $retry->created_at }}
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong> {{ $retry->updated_at }}
                    </div>
                    <div class="mb-3">
                        <strong>Next Attempt At:</strong> {{ $retry->next_attempt_at }}
                    </div>

                    <div class="d-flex gap-2">
                        <a href="/" class="btn btn-secondary">Back to Jobs</a>
                        
                        @if($retry->status === 'running')
                            <a href="/{{ $retry->id }}/cancel" class="btn btn-danger">Cancel Job</a>
                        @endif
                        
                        @if($retry->status !== 'completed' && $retry->attempt < $retry->max_attempts)
                            <a href="/{{ $retry->id }}/retry" class="btn btn-primary">Retry Job</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 