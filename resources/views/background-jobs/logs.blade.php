@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Job Logs</h5>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs" id="logsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main" type="button" role="tab" aria-controls="main" aria-selected="true">
                                Main Log
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="error-tab" data-bs-toggle="tab" data-bs-target="#error" type="button" role="tab" aria-controls="error" aria-selected="false">
                                Error Log
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="logsTabContent">
                        <div class="tab-pane fade show active" id="main" role="tabpanel" aria-labelledby="main-tab">
                            <div class="card">
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">{{ $mainLog }}</pre>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="error" role="tabpanel" aria-labelledby="error-tab">
                            <div class="card">
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;">{{ $errorLog }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 