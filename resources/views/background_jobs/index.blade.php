@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Jobs</h5>
                                    <h2 class="card-text">{{ $stats['total'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Running</h5>
                                    <h2 class="card-text">{{ $stats['running'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Completed</h5>
                                    <h2 class="card-text">{{ $stats['completed'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Failed</h5>
                                    <h2 class="card-text">{{ $stats['failed'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Job Queue</h5>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Class</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Attempt</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($retries as $retry)
                                <tr>
                                    <td>{{ $retry->id }}</td>
                                    <td>{{ $retry->class }}</td>
                                    <td>{{ $retry->method }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $retry->status === 'completed' ? 'success' : 
                                            ($retry->status === 'failed' ? 'danger' : 
                                            ($retry->status === 'running' ? 'info' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($retry->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $retry->attempt }}/{{ $retry->max_attempts }}
                                    </td>
                                    <td>{{ $retry->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="/{{ $retry->id }}" class="btn btn-sm btn-info">
                                            View
                                        </a>
                                        @if($retry->status === 'failed' && $retry->attempt < $retry->max_attempts)
                                            <form action="/{{ $retry->id }}/retry" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">Retry</button>
                                            </form>
                                        @endif
                                        @if($retry->status === 'running')
                                            <form action="/{{ $retry->id }}/cancel" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $retries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 