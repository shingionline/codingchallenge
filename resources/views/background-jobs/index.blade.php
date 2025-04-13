@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Jobs</h5>
                                    <p class="card-text display-4">{{ $stats['total'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Completed</h5>
                                    <p class="card-text display-4">{{ $stats['completed'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Failed</h5>
                                    <p class="card-text display-4">{{ $stats['failed'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Running</h5>
                                    <p class="card-text display-4">{{ $stats['running'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Cancelled</h5>
                                    <p class="card-text display-4">{{ $stats['cancelled'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Class</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Attempt</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($retries as $retry)
                                    <tr>
                                        <td>{{ $retry->id }}</td>
                                        <td>{{ $retry->class }}</td>
                                        <td>{{ $retry->method }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $retry->status === 'completed' ? 'success' : 
                                                ($retry->status === 'failed' ? 'danger' : 
                                                ($retry->status === 'running' ? 'primary' : 
                                                ($retry->status === 'cancelled' ? 'secondary' : 'warning'))) 
                                            }}">
                                                {{ ucfirst($retry->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $retry->attempt }} of {{ $retry->max_attempts }}</td>
                                        <td>{{ $retry->created_at }}</td>
                                        <td>
                                            <a href="/{{ $retry->id }}" class="btn btn-sm btn-info">View</a>
                                            @if($retry->status !== 'completed' && $retry->status !== 'cancelled' && $retry->attempt < $retry->max_attempts)
                                                <a href="/{{ $retry->id }}/retry" class="btn btn-sm btn-primary">Retry</a>
                                            @endif
                                            @if($retry->status === 'running')
                                                <a href="/{{ $retry->id }}/cancel" class="btn btn-sm btn-danger">Cancel</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No jobs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $retries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 