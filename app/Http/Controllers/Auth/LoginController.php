<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'Invalid credentials provided.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        $this->auditLogger->log('auth.login', User::class, $user->id);

        return redirect()->route('dashboard');
    }

    public function destroy(): RedirectResponse
    {
        $this->auditLogger->log('auth.logout');
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
