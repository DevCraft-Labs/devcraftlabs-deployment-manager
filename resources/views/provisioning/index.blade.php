@extends('layouts.app')
@section('content')
<h4 class="mb-3">Database Provisioning</h4>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Host</th><th></th></tr></thead><tbody>
@foreach($connections as $connection)
<tr><td>{{ $connection->name }}</td><td>{{ $connection->host }}:{{ $connection->port }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('provisioning.browse', $connection) }}">Browse</a></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
