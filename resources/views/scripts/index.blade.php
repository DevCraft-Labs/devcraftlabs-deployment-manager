@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div><h4 class="mb-0">Deployment &amp; Monitoring</h4><small class="text-secondary">Manage deployment scripts and their live service health.</small></div>@can('scripts.create')<a class="btn btn-primary" href="{{ route('deployment-scripts.create') }}">New Script</a>@endcan</div>
<div class="row g-3 mb-3"><div class="col-md-4"><div class="card"><div class="card-body"><div class="text-secondary small">Configured Services</div><div class="display-6">{{ $configuredServiceCount }}</div></div></div></div><div class="col-md-4"><div class="card border-success"><div class="card-body"><div class="text-success small">Running</div><div class="display-6 text-success">{{ $runningServiceCount }}</div></div></div></div><div class="col-md-4"><div class="card border-danger"><div class="card-body"><div class="text-danger small">Stopped</div><div class="display-6 text-danger">{{ $stoppedServiceCount }}</div></div></div></div></div>
<form method="GET" action="{{ route('deployment-scripts.index') }}" class="row g-2 mb-3"><input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="direction" value="{{ $direction }}"><div class="col-md-6"><label class="visually-hidden" for="script-search">Search deployment scripts</label><input id="script-search" class="form-control" name="search" value="{{ $search }}" placeholder="Search title or description"></div><div class="col-auto"><button class="btn btn-outline-primary">Search</button></div></form>
@php($nextDirection = $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc')
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th><a class="link-dark text-decoration-none" href="{{ route('deployment-scripts.index', ['search' => $search, 'sort' => 'name', 'direction' => $nextDirection]) }}">Name @if($sort === 'name'){{ $direction === 'asc' ? '↑' : '↓' }}@endif</a></th><th>Cron</th><th>Script</th><th>Service</th><th></th></tr></thead><tbody>
@foreach($scripts as $script)
<tr>
@php($service = $serviceStatuses[$script->id] ?? ['configured' => false, 'isRunning' => false, 'status' => null, 'responseTime' => null, 'error' => null])
<td>{{ $script->name }}<div class="small text-secondary">{{ \Illuminate\Support\Str::limit($script->description, 90) }}</div></td><td>{{ $script->cron_expression ?? '-' }}</td><td>{{ $script->active ? 'Active' : 'Disabled' }}</td><td>@if(!$service['configured'])<span class="badge text-bg-secondary">Not configured</span>@elseif($service['isRunning'])<span class="badge text-bg-success">Running</span><div class="small text-secondary">HTTP {{ $service['status'] }} · {{ $service['responseTime'] }} ms</div>@else<span class="badge text-bg-danger">Stopped</span><div class="small text-danger">{{ $service['error'] ?: 'HTTP ' . ($service['status'] ?? 'unavailable') }}</div>@endif</td>
<td class="text-end d-flex gap-2 justify-content-end">
<a class="btn btn-sm btn-outline-info" href="https://moniytoring-pbu.devcraftlabs.my.id/deployment-scripts/{{ $script->id }}">Monitor</a>
@can('scripts.run')<form method="POST" action="{{ route('deployment-scripts.run', $script) }}">@csrf<button class="btn btn-sm btn-success">Run</button></form>@endcan
@can('scripts.create')<form method="POST" action="{{ route('deployment-scripts.duplicate', $script) }}">@csrf<button class="btn btn-sm btn-warning">Duplicate</button></form>@endcan
@can('scripts.update')<form method="POST" action="{{ route('deployment-scripts.toggle', $script) }}">@csrf<button class="btn btn-sm btn-secondary">Toggle</button></form><a class="btn btn-sm btn-outline-primary" href="{{ route('deployment-scripts.edit', $script) }}">Edit</a>@endcan
@can('scripts.delete')<form method="POST" action="{{ route('deployment-scripts.destroy', $script) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan
</td>
</tr>
@endforeach
</tbody></table></div></div>
<div class="pagination-nav mt-3">{{ $scripts->links() }}</div>
@endsection
