<div class="alert alert-info">This connection is only used for metadata discovery: databases, tables, columns, and indexes. The explorer never exposes data modification actions.</div>
<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Connection Name</label><input class="form-control" name="name" value="{{ old('name', $connection->name ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Host</label><input class="form-control" name="host" value="{{ old('host', $connection->host ?? '127.0.0.1') }}" required></div>
    <div class="col-md-4"><label class="form-label">Port</label><input class="form-control" type="number" name="port" value="{{ old('port', $connection->port ?? 3306) }}" required></div>
    <div class="col-md-4"><label class="form-label">Username</label><input class="form-control" name="username" value="{{ old('username', $connection->username ?? '') }}" required></div>
    <div class="col-md-4"><label class="form-label">Password {{ isset($connection) ? '(leave blank to retain)' : '' }}</label><input class="form-control" type="password" name="password" @required(!isset($connection))></div>
    <div class="col-md-4 form-check mt-5 ms-3"><input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $connection->status ?? true))><label class="form-check-label">Active</label></div>
</div>
