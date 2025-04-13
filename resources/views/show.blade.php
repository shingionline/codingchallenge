@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Job Details</h5>
        <a href="/" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Jobs
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Basic Information</h6>
                <table class="table table-bordered">
                    <tr>
                        <th>Class</th>
                        <td>{{ $retry->class }}</td>
                    </tr>
                    <tr>
                        <th>Method</th>
                        <td>{{ $retry->method }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="status-badge status-{{ $retry->status }}">
                                {{ ucfirst($retry->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Attempts</th>
                        <td>{{ $retry->attempt }}/{{ $retry->max_attempts }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Timing Information</h6>
                <table class="table table-bordered">
                    <tr>
                        <th>Created At</th>
                        <td>{{ $retry->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ $retry->updated_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Next Attempt</th>
                        <td>{{ $retry->next_attempt_at ? $retry->next_attempt_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <h6>Parameters</h6>
                <pre class="bg-light p-3 rounded">{{ json_encode($retry->params, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>

        @if($retry->error)
        <div class="row mt-4">
            <div class="col-12">
                <h6>Error Information</h6>
                <div class="alert alert-danger">
                    <pre class="mb-0">{{ $retry->error }}</pre>
                </div>
            </div>
        </div>
        @endif

        @if($retry->status === 'failed' && $retry->attempt < $retry->max_attempts)
        <div class="row mt-4">
            <div class="col-12">
                <form action="/{{ $retry->id }}/retry" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-clockwise"></i> Retry Job
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection 