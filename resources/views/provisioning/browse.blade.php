@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><div><h4 class="mb-0">{{ $connection->name }} Explorer</h4><small class="text-secondary">{{ $connection->host }}:{{ $connection->port }} · Read-only metadata access</small></div><a class="btn btn-outline-secondary" href="{{ route('provisioning.index') }}">Connections</a></div>
<form class="row g-2 mb-4" method="GET" action="{{ route('provisioning.browse', $connection) }}">
    <div class="col-md-5"><label class="form-label">Database</label><select class="form-select" name="database" onchange="this.form.submit()"><option value="">Select Database</option>@foreach($databases as $db)<option value="{{ $db }}" @selected($database === $db)>{{ $db }}</option>@endforeach</select></div>
    <div class="col-md-5"><label class="form-label">Table</label><select class="form-select" name="table" onchange="this.form.submit()" @disabled(!$database)><option value="">Select Table</option>@foreach($tables as $tb)<option value="{{ $tb }}" @selected($table === $tb)>{{ $tb }}</option>@endforeach</select></div>
</form>
@if($table)
<div class="alert alert-info">Showing schema metadata for <strong>{{ $database }}.{{ $table }}</strong>. No table rows are queried or editable.</div>
<div class="card mb-3"><div class="card-header">Columns</div><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Field</th><th>Type</th><th>Nullable</th><th>Default</th><th>Primary / Key</th><th>Extra</th></tr></thead><tbody>@forelse($description['columns'] as $column)<tr><td>{{ $column->Field }}</td><td>{{ $column->Type }}</td><td>{{ $column->Null }}</td><td>{{ $column->Default ?? '—' }}</td><td>{{ $column->Key ?: '—' }}</td><td>{{ $column->Extra ?: '—' }}</td></tr>@empty<tr><td colspan="6" class="text-center text-secondary">No column metadata found.</td></tr>@endforelse</tbody></table></div></div>
<div class="card"><div class="card-header">Indexes</div><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Name</th><th>Column</th><th>Unique</th><th>Type</th><th>Sequence</th></tr></thead><tbody>@forelse($description['indexes'] as $index)<tr><td>{{ $index->Key_name }}</td><td>{{ $index->Column_name }}</td><td>{{ $index->Non_unique ? 'No' : 'Yes' }}</td><td>{{ $index->Index_type }}</td><td>{{ $index->Seq_in_index }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary">No indexes found.</td></tr>@endforelse</tbody></table></div></div>
@elseif($database)
<div class="card"><div class="card-body text-secondary">Select a table to inspect its columns, key types, defaults, and indexes.</div></div>
@endif
@endsection
