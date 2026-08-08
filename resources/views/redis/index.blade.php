@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Redis Profiles</h4>@can('connections.create')<a class="btn btn-primary" href="{{ route('redis-profiles.create') }}">New Profile</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Host</th><th>Port</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($profiles as $profile)
<tr>
<td>{{ $profile->name }}</td><td>{{ $profile->host }}</td><td>{{ $profile->port }}</td><td>{{ $profile->status ? 'Active' : 'Inactive' }}</td>
<td class="text-end d-flex gap-2 justify-content-end">
@can('connections.test')<form method="POST" action="{{ route('redis-profiles.test', $profile) }}">@csrf<button class="btn btn-sm btn-outline-info">Test</button></form>@endcan
@can('connections.view')<a class="btn btn-sm btn-outline-secondary" href="{{ route('redis-profiles.keys', $profile) }}">Browse Keys</a>@endcan
@can('connections.update')<a class="btn btn-sm btn-outline-primary" href="{{ route('redis-profiles.edit', $profile) }}">Edit</a>@endcan
@can('connections.delete')<form method="POST" action="{{ route('redis-profiles.destroy', $profile) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan
</td>
</tr>
@endforeach
</tbody></table></div></div>
<div class="mt-3">{{ $profiles->links() }}</div>
@endsection
