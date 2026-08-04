@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Redis Profiles</h4><a class="btn btn-primary" href="{{ route('redis-profiles.create') }}">New Profile</a></div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Host</th><th>Port</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($profiles as $profile)
<tr>
<td>{{ $profile->name }}</td><td>{{ $profile->host }}</td><td>{{ $profile->port }}</td><td>{{ $profile->status ? 'Active' : 'Inactive' }}</td>
<td class="text-end d-flex gap-2 justify-content-end">
<form method="POST" action="{{ route('redis-profiles.test', $profile) }}">@csrf<button class="btn btn-sm btn-outline-info">Test</button></form>
<a class="btn btn-sm btn-outline-primary" href="{{ route('redis-profiles.edit', $profile) }}">Edit</a>
<form method="POST" action="{{ route('redis-profiles.destroy', $profile) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
</td>
</tr>
@endforeach
</tbody></table></div></div>
<div class="mt-3">{{ $profiles->links() }}</div>
@endsection
