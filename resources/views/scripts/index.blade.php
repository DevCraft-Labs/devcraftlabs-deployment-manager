@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Deployment Scripts</h4>@can('scripts.create')<a class="btn btn-primary" href="{{ route('deployment-scripts.create') }}">New Script</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Cron</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($scripts as $script)
<tr>
<td>{{ $script->name }}</td><td>{{ $script->cron_expression ?? '-' }}</td><td>{{ $script->active ? 'Active' : 'Disabled' }}</td>
<td class="text-end d-flex gap-2 justify-content-end">
@can('scripts.run')<form method="POST" action="{{ route('deployment-scripts.run', $script) }}">@csrf<button class="btn btn-sm btn-success">Run</button></form>@endcan
@can('scripts.create')<form method="POST" action="{{ route('deployment-scripts.duplicate', $script) }}">@csrf<button class="btn btn-sm btn-warning">Duplicate</button></form>@endcan
@can('scripts.update')<form method="POST" action="{{ route('deployment-scripts.toggle', $script) }}">@csrf<button class="btn btn-sm btn-secondary">Toggle</button></form><a class="btn btn-sm btn-outline-primary" href="{{ route('deployment-scripts.edit', $script) }}">Edit</a>@endcan
@can('scripts.delete')<form method="POST" action="{{ route('deployment-scripts.destroy', $script) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan
</td>
</tr>
@endforeach
</tbody></table></div></div>
<div class="mt-3">{{ $scripts->links() }}</div>
@endsection
