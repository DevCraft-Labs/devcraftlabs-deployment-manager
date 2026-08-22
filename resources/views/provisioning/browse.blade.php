@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><div><h4 class="mb-0">{{ $connection->name }} Explorer</h4><small class="text-secondary">{{ $connection->host }}:{{ $connection->port }} · Read-only metadata access</small></div><a class="btn btn-outline-secondary" href="{{ route('provisioning.index') }}">Connections</a></div>
<form class="row g-2 mb-4" method="GET" action="{{ route('provisioning.browse', $connection) }}">
    <div class="col-md-5"><label class="form-label">Database</label><select class="form-select" name="database" onchange="this.form.submit()"><option value="">Select Database</option>@foreach($databases as $db)<option value="{{ $db }}" @selected($database === $db)>{{ $db }}</option>@endforeach</select></div>
    <div class="col-md-5"><label class="form-label">Table</label><select class="form-select" name="table" onchange="this.form.submit()" @disabled(!$database)><option value="">Select Table</option>@foreach($tables as $tb)<option value="{{ $tb }}" @selected($table === $tb)>{{ $tb }}</option>@endforeach</select></div>
</form>
@if($table)
@php
    $primaryKeyColumns = collect($description['indexes'])->where('Key_name', 'PRIMARY')->sortBy('Seq_in_index')->pluck('Column_name')->all();
@endphp
<div class="alert alert-info">Working with <strong>{{ $database }}.{{ $table }}</strong>. Every data change is audited.</div>
@if($primaryKeyColumns)
    @can('provisioning.data.create')
    <div class="card mb-3"><div class="card-header">Add Row</div><div class="card-body"><form method="POST" action="{{ route('provisioning.rows.store', $connection) }}">@csrf<input type="hidden" name="database" value="{{ $database }}"><input type="hidden" name="table" value="{{ $table }}"><div class="row g-3">
        @foreach($description['columns'] as $column)
            @if(!str_contains(strtolower($column->Extra), 'auto_increment') && !str_contains(strtolower($column->Extra), 'generated'))
            <div class="col-md-4"><label class="form-label">{{ $column->Field }} <small class="text-secondary">{{ $column->Type }}</small></label><input class="form-control" name="data[{{ $column->Field }}]" @required($column->Null === 'NO' && $column->Default === null)></div>
            @endif
        @endforeach
    </div><div class="mt-3"><button class="btn btn-primary">Create Row</button></div></form></div></div>
    @endcan
    <div class="card mb-3"><div class="card-header d-flex justify-content-between"><span>Rows</span><span>{{ $rows->total() }} total</span></div><div class="table-responsive"><table class="table table-striped align-middle mb-0"><thead><tr>@foreach($description['columns'] as $column)<th>{{ $column->Field }}</th>@endforeach
        @if(auth()->user()?->can('provisioning.data.update') || auth()->user()?->can('provisioning.data.delete'))<th></th>@endif</tr></thead><tbody>
        @forelse($rows as $row)
            @php($key = base64_encode(json_encode(array_intersect_key((array) $row, array_flip($primaryKeyColumns)))))
            <tr>@foreach($description['columns'] as $column)<td class="text-break">{{ \Illuminate\Support\Str::limit((string) ($row->{$column->Field} ?? ''), 80) }}</td>@endforeach
                @if(auth()->user()?->can('provisioning.data.update') || auth()->user()?->can('provisioning.data.delete'))<td class="text-end text-nowrap">@can('provisioning.data.update')<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRow{{ $loop->index }}">Edit</button>@endcan @can('provisioning.data.delete')<form class="d-inline" method="POST" action="{{ route('provisioning.rows.destroy', $connection) }}" onsubmit="return confirm('Delete this row?')">@csrf @method('DELETE')<input type="hidden" name="database" value="{{ $database }}"><input type="hidden" name="table" value="{{ $table }}"><input type="hidden" name="key" value="{{ $key }}"><button class="btn btn-sm btn-outline-danger">Delete</button></form>@endcan</td>@endif</tr>
        @empty
            <tr><td colspan="{{ count($description['columns']) + 1 }}" class="text-center text-secondary py-4">No rows found.</td></tr>
        @endforelse
    </tbody></table></div></div>
    <div class="mt-3">{{ $rows->withQueryString()->links() }}</div>
    @can('provisioning.data.update')
        @foreach($rows as $row)
            @php($key = base64_encode(json_encode(array_intersect_key((array) $row, array_flip($primaryKeyColumns)))))
            <div class="modal fade" id="editRow{{ $loop->index }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('provisioning.rows.update', $connection) }}">@csrf @method('PUT')<input type="hidden" name="database" value="{{ $database }}"><input type="hidden" name="table" value="{{ $table }}"><input type="hidden" name="key" value="{{ $key }}"><div class="modal-header"><h5 class="modal-title">Edit Row</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">@foreach($description['columns'] as $column)<div class="col-md-6"><label class="form-label">{{ $column->Field }}</label><input class="form-control" name="data[{{ $column->Field }}]" value="{{ $row->{$column->Field} ?? '' }}" @disabled(str_contains(strtolower($column->Extra), 'auto_increment') || str_contains(strtolower($column->Extra), 'generated'))></div>@endforeach</div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Row</button></div></form></div></div></div>
        @endforeach
    @endcan
@else
    <div class="alert alert-warning">This table has no primary key, so row editing and deletion are unavailable.</div>
@endif
<div class="card mb-3"><div class="card-header">Columns</div><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Field</th><th>Type</th><th>Nullable</th><th>Default</th><th>Primary / Key</th><th>Extra</th></tr></thead><tbody>@forelse($description['columns'] as $column)<tr><td>{{ $column->Field }}</td><td>{{ $column->Type }}</td><td>{{ $column->Null }}</td><td>{{ $column->Default ?? '—' }}</td><td>{{ $column->Key ?: '—' }}</td><td>{{ $column->Extra ?: '—' }}</td></tr>@empty<tr><td colspan="6" class="text-center text-secondary">No column metadata found.</td></tr>@endforelse</tbody></table></div></div>
<div class="card"><div class="card-header">Indexes</div><div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Name</th><th>Column</th><th>Unique</th><th>Type</th><th>Sequence</th></tr></thead><tbody>@forelse($description['indexes'] as $index)<tr><td>{{ $index->Key_name }}</td><td>{{ $index->Column_name }}</td><td>{{ $index->Non_unique ? 'No' : 'Yes' }}</td><td>{{ $index->Index_type }}</td><td>{{ $index->Seq_in_index }}</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary">No indexes found.</td></tr>@endforelse</tbody></table></div></div>
@elseif($database)
<div class="card"><div class="card-body text-secondary">Select a table to inspect its columns, key types, defaults, and indexes.</div></div>
@endif
@endsection
