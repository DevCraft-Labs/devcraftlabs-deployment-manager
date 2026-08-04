@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Telegram Connections</h4>@can('connections.create')<a class="btn btn-primary" href="{{ route('telegram-connections.create') }}">New Connection</a>@endcan</div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Chat ID</th><th></th></tr></thead><tbody>
@foreach($connections as $connection)
<tr><td>{{ $connection->name }}</td><td>{{ $connection->chat_id }}</td>
<td class="text-end d-flex gap-2 justify-content-end">
@can('connections.test')<form method="POST" action="{{ route('telegram-connections.test', $connection) }}">@csrf<button class="btn btn-sm btn-outline-info">Test</button></form>@endcan
@can('connections.update')<a class="btn btn-sm btn-outline-primary" href="{{ route('telegram-connections.edit', $connection) }}">Edit</a>@endcan
@can('connections.delete')<form method="POST" action="{{ route('telegram-connections.destroy', $connection) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan
</td></tr>
@endforeach
</tbody></table></div></div>
<div class="mt-3">{{ $connections->links() }}</div>
@endsection
