@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between mb-3"><h4>SMTP Profiles</h4>@can('connections.create')<a class="btn btn-primary" href="{{ route('smtp-profiles.create') }}">New Profile</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Host</th><th>From</th><th></th></tr></thead><tbody>
@foreach($profiles as $profile)
<tr>
<td>{{ $profile->name }}</td><td>{{ $profile->host }}:{{ $profile->port }}</td><td>{{ $profile->from_email }}</td>
<td class="text-end d-flex gap-2 justify-content-end">
@can('connections.test')<form method="POST" action="{{ route('smtp-profiles.test', $profile) }}">@csrf<input type="hidden" name="recipient_email" value="{{ auth()->user()->username }}@example.com"><button class="btn btn-sm btn-outline-info">Test</button></form>@endcan
@can('connections.update')<a class="btn btn-sm btn-outline-primary" href="{{ route('smtp-profiles.edit', $profile) }}">Edit</a>@endcan
@can('connections.delete')<form method="POST" action="{{ route('smtp-profiles.destroy', $profile) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan
</td></tr>
@endforeach
</tbody></table></div></div>
<div class="mt-3">{{ $profiles->links() }}</div>
@endsection
