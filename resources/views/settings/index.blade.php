@extends('layouts.app')
@section('content')
<h4 class="mb-3">Application Settings</h4>
<form method="POST" action="{{ route('settings.update') }}" class="card card-body">@csrf @method('PATCH')
<div class="row g-3 mb-3">
<div class="col-md-6"><label class="form-label">Server Name</label><input class="form-control" name="server_name" value="{{ old('server_name', $setting->server_name) }}"></div>
<div class="col-md-6"><label class="form-label">Timezone</label><input class="form-control" name="timezone" value="{{ old('timezone', $setting->timezone) }}"></div>
<div class="col-md-4"><label class="form-label">Default Telegram Notification Channel</label><select class="form-select" name="default_telegram_connection_id"><option value="">None</option>@foreach($telegramConnections as $item)<option value="{{ $item->id }}" @selected($setting->default_telegram_connection_id == $item->id)>{{ $item->name }}</option>@endforeach</select><div class="form-text">Receives deployment completion notifications unless a script has its own Telegram connection.</div></div>
<div class="col-md-4"><label class="form-label">Default SMTP</label><select class="form-select" name="default_smtp_profile_id"><option value="">None</option>@foreach($smtpProfiles as $item)<option value="{{ $item->id }}" @selected($setting->default_smtp_profile_id == $item->id)>{{ $item->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Default Redis</label><select class="form-select" name="default_redis_profile_id"><option value="">None</option>@foreach($redisProfiles as $item)<option value="{{ $item->id }}" @selected($setting->default_redis_profile_id == $item->id)>{{ $item->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Retention Days</label><input class="form-control" type="number" name="retention_days" value="{{ old('retention_days', $setting->retention_days) }}"></div>
<div class="col-md-3 form-check mt-5 ms-3"><input class="form-check-input" type="checkbox" name="log_cleanup" value="1" @checked($setting->log_cleanup)><label class="form-check-label">Enable Log Cleanup</label></div>
</div>
@can('settings.manage')<button class="btn btn-primary">Save Settings</button>@else<button class="btn btn-secondary" disabled>Read-only access</button>@endcan
</form>
<div class="mt-4 d-flex gap-2">
    @can('reports.export')
    <a class="btn btn-success" href="{{ route('reports.download') }}">Download XLSX Report</a>
    <form method="POST" action="{{ route('reports.queue') }}">@csrf<button class="btn btn-outline-success">Queue XLSX Generation</button></form>
    @endcan
</div>
@endsection
