@extends('layouts.app')
@section('content')
<h4 class="mb-3">Cron Manager</h4>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Script</th><th>Expression</th><th>Enabled</th><th>Next Run (preview)</th><th></th></tr></thead><tbody>
@foreach($scripts as $script)
<tr>
<td>{{ $script->name }}</td><td>{{ $script->cron_expression }}</td><td>{{ $script->cron_enabled ? 'Yes' : 'No' }}</td><td>-</td>
<td>
<form method="POST" action="{{ route('cron.update', $script) }}" class="d-flex gap-2">@csrf @method('PATCH')
<input class="form-control form-control-sm" name="cron_expression" value="{{ $script->cron_expression }}">
<select name="cron_enabled" class="form-select form-select-sm"><option value="1">Enable</option><option value="0">Disable</option></select>
<button class="btn btn-sm btn-primary">Save</button>
</form>
</td>
</tr>
@endforeach
</tbody></table></div></div>
@endsection
