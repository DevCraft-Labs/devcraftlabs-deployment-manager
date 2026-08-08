@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="mb-0">Server Monitoring</h4><small class="text-secondary">Live HTTP health checks for configured Laravel deployments.</small></div>
    <button class="btn btn-outline-secondary" onclick="window.location.reload()">Refresh status</button>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-secondary small">Configured Servers</div><div class="display-6">{{ $servers->count() }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-success"><div class="card-body"><div class="text-success small">Running</div><div class="display-6 text-success">{{ $runningCount }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-danger"><div class="card-body"><div class="text-danger small">Stopped</div><div class="display-6 text-danger">{{ $stoppedCount }}</div></div></div></div>
</div>
<div class="card"><div class="table-responsive"><table class="table table-striped align-middle mb-0">
    <thead><tr><th>Deployment</th><th>Health-check URL</th><th>Status</th><th>HTTP</th><th>Response Time</th><th>Details</th></tr></thead>
    <tbody>@forelse($servers as $server)
        <tr>
            <td><a href="{{ route('deployment-scripts.show', $server['script']) }}">{{ $server['script']->name }}</a></td>
            <td><a href="{{ $server['script']->health_check_url }}" target="_blank" rel="noopener noreferrer">{{ $server['script']->health_check_url }}</a></td>
            <td><span class="badge text-bg-{{ $server['isRunning'] ? 'success' : 'danger' }}">{{ $server['isRunning'] ? 'Running' : 'Stopped' }}</span></td>
            <td>{{ $server['status'] ?? '—' }}</td><td>{{ $server['responseTime'] === null ? '—' : $server['responseTime'] . ' ms' }}</td>
            <td class="text-danger small">{{ $server['error'] }}</td>
        </tr>
    @empty<tr><td colspan="6" class="text-center text-secondary py-4">No server health-check URLs have been configured. Add one to a Deployment Script.</td></tr>@endforelse</tbody>
</table></div></div>
@endsection

@push('scripts')
<script>window.setTimeout(() => window.location.reload(), 30000);</script>
@endpush