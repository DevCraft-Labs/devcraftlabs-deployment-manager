<div class="row g-3 mb-3">
<div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $profile->name ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">Host</label><input class="form-control" name="host" value="{{ old('host', $profile->host ?? '') }}" required></div>
<div class="col-md-3"><label class="form-label">Port</label><input class="form-control" type="number" name="port" value="{{ old('port', $profile->port ?? 587) }}"></div>
<div class="col-md-3"><label class="form-label">Encryption</label><select class="form-select" name="encryption"><option value="tls">tls</option><option value="ssl">ssl</option><option value="starttls">starttls</option><option value="none">none</option></select></div>
<div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" value="{{ old('username', $profile->username ?? '') }}"></div>
<div class="col-md-6"><label class="form-label">Password</label><input class="form-control" type="password" name="password"></div>
<div class="col-md-6"><label class="form-label">From Email</label><input class="form-control" name="from_email" value="{{ old('from_email', $profile->from_email ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">From Name</label><input class="form-control" name="from_name" value="{{ old('from_name', $profile->from_name ?? '') }}" required></div>
<div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $profile->description ?? '') }}</textarea></div>
<div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $profile->status ?? true))><label class="form-check-label">Active</label></div>
</div>
