<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $profile->name ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Host</label><input class="form-control" name="host" value="{{ old('host', $profile->host ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Port</label><input class="form-control" name="port" type="number" value="{{ old('port', $profile->port ?? 6379) }}"></div>
    <div class="col-md-3"><label class="form-label">Database</label><input class="form-control" name="database" type="number" value="{{ old('database', $profile->database ?? 0) }}"></div>
    <div class="col-md-3"><label class="form-label">Timeout</label><input class="form-control" name="timeout" type="number" value="{{ old('timeout', $profile->timeout ?? 5) }}"></div>
    <div class="col-md-3"><label class="form-label">Default TTL</label><input class="form-control" name="default_ttl" type="number" value="{{ old('default_ttl', $profile->default_ttl ?? 300) }}"></div>
    <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" value="{{ old('username', $profile->username ?? '') }}"></div>
    <div class="col-md-6"><label class="form-label">Password</label><input class="form-control" name="password" type="password" placeholder="********"></div>
    <div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $profile->description ?? '') }}</textarea></div>
    <div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" name="tls" value="1" @checked(old('tls', $profile->tls ?? false))><label class="form-check-label">TLS</label></div>
    <div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $profile->status ?? true))><label class="form-check-label">Active</label></div>
</div>
