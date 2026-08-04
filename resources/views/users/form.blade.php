<div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" value="{{ old('username', $managedUser->username ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Display Name</label><input class="form-control" name="name" value="{{ old('name', $managedUser->name ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Password {{ isset($managedUser) ? '(leave blank to retain)' : '' }}</label><input class="form-control" type="password" name="password" @required(!isset($managedUser))></div>
    <div class="col-md-6"><label class="form-label">Confirm Password</label><input class="form-control" type="password" name="password_confirmation" @required(!isset($managedUser))></div>
    <div class="col-md-4"><label class="form-label">Role</label><select class="form-select" name="role" required>@php($selectedRole = old('role', isset($managedUser) ? $managedUser->roles->first()?->name : 'Developer'))@foreach(['Owner', 'Developer', 'Viewer'] as $role)<option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role }}</option>@endforeach</select></div>
    <div class="col-md-4 form-check mt-5 ms-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $managedUser->is_active ?? true))><label class="form-check-label">Active account</label></div>
</div>
