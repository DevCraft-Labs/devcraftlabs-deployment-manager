@extends('layouts.app')
@section('content')
<div class="card card-body mb-3"><div class="d-flex justify-content-between align-items-start"><div><h4>{{ $script->name }}</h4><p class="mb-2">{{ $script->description }}</p><small class="text-secondary">Health URL: {{ $script->health_check_url ?: 'Not configured' }}</small></div><a href="{{ route('deployment-scripts.application-logs', $script) }}" class="btn btn-outline-secondary">Download Application Logs</a></div></div>
<div class="card"><div class="card-header">Execution History</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Started</th><th>Duration</th><th>Status</th><th>Exit</th><th>Failure Reason</th><th></th></tr></thead><tbody>
@foreach($script->executions as $execution)
<tr><td>{{ $execution->started_at }}</td><td>{{ $execution->duration_ms }} ms</td><td>{{ $execution->is_success ? 'Success' : 'Failed' }}</td><td>{{ $execution->exit_code }}</td><td>{{ $execution->is_success ? '—' : \Illuminate\Support\Str::limit($execution->stderr ?: 'See downloaded failure report.', 100) }}</td><td><a href="{{ route('deployments.logs', $execution) }}" class="btn btn-sm btn-outline-secondary">Download Log</a></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
