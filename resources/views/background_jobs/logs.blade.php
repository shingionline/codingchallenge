@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Job Logs</h5>
        <a href="/" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Jobs
        </a>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="logTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="main-tab" data-bs-toggle="tab" href="#main" role="tab">
                    Main Log
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="error-tab" data-bs-toggle="tab" href="#error" role="tab">
                    Error Log
                </a>
            </li>
        </ul>
        <div class="tab-content mt-3" id="logTabsContent">
            <div class="tab-pane fade show active" id="main" role="tabpanel">
                <pre class="bg-light p-3 rounded">{{ $mainLog }}</pre>
            </div>
            <div class="tab-pane fade" id="error" role="tabpanel">
                <pre class="bg-light p-3 rounded">{{ $errorLog }}</pre>
            </div>
        </div>
    </div>
</div>

<style>
.log-container {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 1rem;
    max-height: 600px;
    overflow-y: auto;
}

.log-content {
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
    font-family: monospace;
    font-size: 0.9rem;
    line-height: 1.4;
    color: #212529;
}

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    font-weight: bold;
    color: #0d6efd;
    border-color: #dee2e6 #dee2e6 #fff;
}

/* Style for JSON content */
.log-content {
    color: #212529;
}

.log-content .json-key {
    color: #0d6efd;
}

.log-content .json-string {
    color: #198754;
}

.log-content .json-number {
    color: #fd7e14;
}

.log-content .json-boolean {
    color: #6f42c1;
}

.log-content .json-null {
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format JSON content in logs
    const logContents = document.querySelectorAll('.log-content');
    logContents.forEach(content => {
        const text = content.textContent;
        try {
            const json = JSON.parse(text);
            content.innerHTML = syntaxHighlight(JSON.stringify(json, null, 2));
        } catch (e) {
            // Not JSON, leave as is
        }
    });
});

function syntaxHighlight(json) {
    if (typeof json != 'string') {
        json = JSON.stringify(json, undefined, 2);
    }
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        let cls = 'json-number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'json-key';
            } else {
                cls = 'json-string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'json-boolean';
        } else if (/null/.test(match)) {
            cls = 'json-null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}
</script>
@endsection 