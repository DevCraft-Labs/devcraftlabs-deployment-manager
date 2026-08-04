@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><h4 class="mb-0">User Management</h4><small class="text-secondary">Owner-only access control</small></div>
    <a class="btn btn-primary" href="{{ route('users.create') }}">Create User</a>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
<thead><tr><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th></tr></thead>
<tbody>
@forelse($users as $user)
<tr>
<td>{{ $user->username }}</td><td>{{ $user->name }}</td>
<td>{{ $user->roles->pluck('name')->join(', ') ?: 'Unassigned' }}</td>
<td><span class="badge text-bg-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Active' : 'Disabled' }}</span></td>
<td>{{ $user->last_login_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
<td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user) }}">Edit</a>
<form class="d-inline" method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" @disabled($user->is(auth()->user()))>Delete</button></form></td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-secondary">No users found.</td></tr>
@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
