@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><div><h4 class="mb-0">Database Provisioning Explorer</h4><small class="text-secondary">Read-only schema discovery; no data mutations are available.</small></div>@can('provisioning.create')<a class="btn btn-primary" href="{{ route('provisioning.create') }}">Add MySQL Connection</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Host</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
@foreach($connections as $connection)
<tr><td>{{ $connection->name }}</td><td>{{ $connection->host }}:{{ $connection->port }}</td><td><span class="badge text-bg-{{ $connection->status ? 'success' : 'secondary' }}">{{ $connection->status ? 'Active' : 'Disabled' }}</span></td><td class="text-end"><a class="btn btn-sm btn-primary" href="{{ route('provisioning.browse', $connection) }}">Open Explorer</a>@can('provisioning.create')<form class="d-inline" method="POST" action="{{ route('provisioning.test', $connection) }}">@csrf<button class="btn btn-sm btn-outline-info">Test</button></form>@endcan @can('provisioning.update')<a class="btn btn-sm btn-outline-primary" href="{{ route('provisioning.edit', $connection) }}">Edit</a>@endcan @can('provisioning.delete')<form class="d-inline" method="POST" action="{{ route('provisioning.destroy', $connection) }}" onsubmit="return confirm('Delete this connection?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan</td></tr>
@endforeach
</tbody></table></div></div>
@endsection
