<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->with('roles')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);
        $data['is_active'] = $request->boolean('is_active');

        $user = User::query()->create($data);
        $user->syncRoles([$role]);

        $this->auditLogger->log('user.create', User::class, $user->id, ['role' => $role]);

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['managedUser' => $user->load('roles')]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        unset($data['role']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        if ($user->is(Auth::user()) && !$data['is_active']) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($user->is(Auth::user()) && $role !== 'Owner') {
            return back()->withErrors(['role' => 'You cannot remove your own Owner role.']);
        }

        if ($user->hasRole('Owner') && $role !== 'Owner' && User::role('Owner')->count() <= 1) {
            return back()->withErrors(['role' => 'At least one Owner account must remain.']);
        }

        $user->update($data);
        $user->syncRoles([$role]);

        $this->auditLogger->log('user.update', User::class, $user->id, ['role' => $role]);

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->hasRole('Owner') && User::role('Owner')->count() <= 1) {
            return back()->withErrors(['user' => 'At least one Owner account must remain.']);
        }

        $id = $user->id;
        $user->delete();
        $this->auditLogger->log('user.delete', User::class, $id);

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }
}
