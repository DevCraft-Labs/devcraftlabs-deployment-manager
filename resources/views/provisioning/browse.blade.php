@extends('layouts.app')
@section('content')
<h4 class="mb-3">{{ $connection->name }} - Read Only Explorer</h4>
<form class="row g-2 mb-3">
    <div class="col-md-5"><select class="form-select" name="database" onchange="this.form.submit()"><option value="">Select Database</option>@foreach($databases as $db)<option value="{{ $db }}" @selected($database === $db)>{{ $db }}</option>@endforeach</select></div>
    <div class="col-md-5"><select class="form-select" name="table" onchange="this.form.submit()"><option value="">Select Table</option>@foreach($tables as $tb)<option value="{{ $tb }}" @selected($table === $tb)>{{ $tb }}</option>@endforeach</select></div>
</form>
@if($table)
<div class="card mb-3"><div class="card-header">Columns</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr></thead><tbody>@foreach($description['columns'] as $column)<tr><td>{{ $column->Field }}</td><td>{{ $column->Type }}</td><td>{{ $column->Null }}</td><td>{{ $column->Key }}</td></tr>@endforeach</tbody></table></div></div>
<div class="card"><div class="card-header">Indexes</div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Column</th><th>Unique</th></tr></thead><tbody>@foreach($description['indexes'] as $index)<tr><td>{{ $index->Key_name }}</td><td>{{ $index->Column_name }}</td><td>{{ $index->Non_unique ? 'No' : 'Yes' }}</td></tr>@endforeach</tbody></table></div></div>
@endif
@endsection
