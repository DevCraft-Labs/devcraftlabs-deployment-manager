@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Deployments</h4>
        <small class="text-secondary">Monitor active deployments, application health, and completed deployment logs.</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Back to Dashboard</a>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between"><span>Deployment Queue</span><span class="badge text-bg-secondary">{{ $queuedExecutions->count() }} active</span></div>
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>Status</th><th>Script</th><th>Server Health</th><th>Triggered By</th><th>Source</th><th>Queued / Started</th><th>Elapsed</th></tr></thead>
            <tbody>
            @forelse($queuedExecutions as $execution)
                @php($health = $healthStatuses[$execution->deployment_script_id] ?? ['state' => 'not-configured', 'label' => 'Not configured'])
                <tr>
                    <td><span class="badge text-bg-{{ $execution->status === 'running' ? 'primary' : 'warning' }}">{{ ucfirst($execution->status) }}</span></td>
                    <td>@if($execution->script)<a href="{{ route('deployment-scripts.show', $execution->script) }}">{{ $execution->script->name }}</a>@else Deleted script @endif</td>
                    <td><span class="badge text-bg-{{ $health['state'] === 'running' ? 'success' : ($health['state'] === 'stopped' ? 'danger' : 'secondary') }}">{{ $health['label'] }}</span></td>
                    <td>{{ $execution->user?->username ?? 'System' }}</td>
                    <td>{{ ucfirst($execution->triggered_via) }}</td>
                    <td>{{ $execution->started_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $execution->started_at?->diffForHumans(null, true) ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">No queued or running deployments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">Deployment History</div>
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>Completed</th><th>Script</th><th>Health</th><th>Result</th><th>Duration</th><th>Log Preview</th><th></th></tr></thead>
            <tbody>
            @forelse($historyExecutions as $execution)
                @php($health = $healthStatuses[$execution->deployment_script_id] ?? ['state' => 'not-configured', 'label' => 'Not configured'])
                @php($logPreview = $execution->failure_report ?: $execution->stderr ?: $execution->stdout ?: 'No output was captured.')
                <tr>
                    <td>{{ $execution->finished_at?->format('Y-m-d H:i:s') }}</td>
                    <td>@if($execution->script)<a href="{{ route('deployment-scripts.show', $execution->script) }}">{{ $execution->script->name }}</a>@else Deleted script @endif</td>
                    <td><span class="badge text-bg-{{ $health['state'] === 'running' ? 'success' : ($health['state'] === 'stopped' ? 'danger' : 'secondary') }}">{{ $health['label'] }}</span></td>
                    <td><span class="badge text-bg-{{ $execution->is_success ? 'success' : 'danger' }}">{{ $execution->is_success ? 'Succeeded' : 'Failed' }}</span></td>
                    <td>{{ $execution->duration_ms === null ? '—' : $execution->duration_ms . ' ms' }}</td>
                    <td class="text-break" style="max-width: 360px;">{{ \Illuminate\Support\Str::limit($logPreview, 140) }}</td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#executionLog{{ $execution->id }}">View Full Log</button></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">No completed deployment history.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $historyExecutions->links() }}</div>

@foreach($historyExecutions as $execution)
<div class="modal fade" id="executionLog{{ $execution->id }}" tabindex="-1" aria-labelledby="executionLogLabel{{ $execution->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="executionLogLabel{{ $execution->id }}">Deployment #{{ $execution->id }} Log</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            @if($execution->failure_report)<h6>Failure Report</h6><pre class="bg-body-tertiary border rounded p-3 text-wrap">{{ $execution->failure_report }}</pre>@endif
            <h6>Standard Output</h6><pre class="bg-body-tertiary border rounded p-3 text-wrap">{{ $execution->stdout ?: 'No standard output captured.' }}</pre>
            <h6>Standard Error</h6><pre class="bg-body-tertiary border rounded p-3 text-wrap">{{ $execution->stderr ?: 'No standard error captured.' }}</pre>
        </div>
        <div class="modal-footer"><a class="btn btn-outline-secondary" href="{{ route('deployments.logs', $execution) }}">Download Log</a><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button></div>
    </div></div>
</div>
@endforeach
@endsection

@if($queuedExecutions->isNotEmpty())
@push('scripts')
<script>
    window.setTimeout(() => window.location.reload(), 10000);
</script>
@endpush
@endif
