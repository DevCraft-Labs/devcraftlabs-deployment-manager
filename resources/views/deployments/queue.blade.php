@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Deployment Queue</h4>
        <small class="text-secondary">Queued and actively running script executions.</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Back to Dashboard</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>Status</th><th>Script</th><th>Triggered By</th><th>Source</th><th>Queued / Started</th><th>Elapsed</th></tr></thead>
            <tbody>
            @forelse($executions as $execution)
                <tr>
                    <td><span class="badge text-bg-{{ $execution->status === 'running' ? 'primary' : 'warning' }}">{{ ucfirst($execution->status) }}</span></td>
                    <td><a href="{{ route('deployment-scripts.show', $execution->script) }}">{{ $execution->script?->name ?? 'Deleted script' }}</a></td>
                    <td>{{ $execution->user?->username ?? 'System' }}</td>
                    <td>{{ ucfirst($execution->triggered_via) }}</td>
                    <td>{{ $execution->started_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $execution->started_at?->diffForHumans(null, true) ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">No queued or running deployments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $executions->links() }}</div>
@endsection

@push('scripts')
<script>
    window.setTimeout(() => window.location.reload(), 10000);
</script>
@endpush
