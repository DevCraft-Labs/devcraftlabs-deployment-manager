@extends('layouts.app')
@section('content')
<div class="card card-body mb-3"><h4>{{ $script->name }}</h4><p>{{ $script->description }}</p></div>
<div class="card"><div class="card-header">Execution History</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Started</th><th>Duration</th><th>Status</th><th>Exit</th><th></th></tr></thead><tbody>
@foreach($script->executions as $execution)
<tr><td>{{ $execution->started_at }}</td><td>{{ $execution->duration_ms }} ms</td><td>{{ $execution->is_success ? 'Success' : 'Failed' }}</td><td>{{ $execution->exit_code }}</td><td><a href="{{ route('deployments.logs', $execution) }}" class="btn btn-sm btn-outline-secondary">Download Log</a></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
