@extends('layouts.app')
@section('content')
<div class="card card-body mb-3"><div class="d-flex justify-content-between align-items-start gap-3"><div><h4>{{ $script->name }}</h4><p class="mb-2">{{ $script->description }}</p><small class="text-secondary">Health URL: {{ $script->health_check_url ?: 'Not configured' }}</small></div><div class="d-flex flex-wrap gap-2">@can('scripts.update')<a href="{{ route('deployment-scripts.environment.edit', $script) }}" class="btn btn-primary">Edit .env</a>@endcan<a href="{{ route('deployment-scripts.application-logs', $script) }}" class="btn btn-outline-secondary">Download Application Logs</a></div></div></div>
<div class="card mb-3"><div class="card-header">Application Log Files</div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Last Updated</th><th>Size</th><th></th></tr></thead><tbody>@forelse($logFiles as $logFile)<tr><td class="text-break">{{ $logFile['name'] }}</td><td>{{ $logFile['updatedAt']->format('d M Y H:i:s') }}</td><td>{{ number_format($logFile['size'] / 1024, 1) }} KB</td><td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-log-tail-url="{{ route('deployment-scripts.log-file.tail', ['deploymentScript' => $script, 'file' => $logFile['name']]) }}" data-log-name="{{ $logFile['name'] }}" data-bs-toggle="modal" data-bs-target="#logTailModal">Tail</button><a class="btn btn-sm btn-outline-secondary" href="{{ route('deployment-scripts.log-file.download', ['deploymentScript' => $script, 'file' => $logFile['name']]) }}">Download</a></td></tr>@empty<tr><td colspan="4" class="text-center text-secondary py-4">No readable files were found in the configured log directory.</td></tr>@endforelse</tbody></table></div></div>
<div class="modal fade" id="logTailModal" tabindex="-1" aria-labelledby="logTailTitle" aria-hidden="true"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="logTailTitle">Log Tail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><pre class="bg-dark text-light m-0 p-3" id="logTailContents" style="max-height: 60vh; overflow: auto; white-space: pre-wrap;"></pre></div></div></div>
<div class="card"><div class="card-header">Execution History</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Started</th><th>Duration</th><th>Status</th><th>Exit</th><th>Failure Reason</th><th></th></tr></thead><tbody>
@foreach($script->executions as $execution)
<tr><td>{{ $execution->started_at }}</td><td>{{ $execution->duration_ms }} ms</td><td>{{ $execution->is_success ? 'Success' : 'Failed' }}</td><td>{{ $execution->exit_code }}</td><td>{{ $execution->is_success ? '—' : \Illuminate\Support\Str::limit($execution->stderr ?: 'See downloaded failure report.', 100) }}</td><td><a href="{{ route('deployments.logs', $execution) }}" class="btn btn-sm btn-outline-secondary">Download Log</a></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
@push('scripts')
<script>
	let logTailInterval;
	const logTailModal = document.getElementById('logTailModal');
	const logTailContents = document.getElementById('logTailContents');
	logTailModal.addEventListener('show.bs.modal', (event) => {
		const button = event.relatedTarget;
		const loadTail = () => fetch(button.dataset.logTailUrl).then(response => response.text()).then(contents => { logTailContents.textContent = contents; });
		document.getElementById('logTailTitle').textContent = `Tail: ${button.dataset.logName}`;
		loadTail();
		logTailInterval = window.setInterval(loadTail, 3000);
	});
	logTailModal.addEventListener('hidden.bs.modal', () => window.clearInterval(logTailInterval));
</script>
@endpush
