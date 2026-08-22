<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('dark_mode') === '1' }" x-bind:data-bs-theme="darkMode ? 'dark' : 'light'">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DevCraft Labs CPanel Deployment Manager') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/devcraft-labs-logo.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --metro-blue: #0078d4; --metro-teal: #008272; --metro-charcoal: #222; --metro-paper: #f3f3f3; --metro-orange: #d83b01; }
        body { background: var(--metro-paper); min-height: 100vh; font-family: "Segoe UI", Tahoma, sans-serif; }
        [data-bs-theme='dark'] body { background: #161616; }
        .sidebar { width: 260px; min-height: 100vh; background: var(--metro-charcoal); }
        .main-panel { min-height: 100vh; padding: 2rem; }
        .glass { background: #fff; border-left: 5px solid var(--metro-blue); box-shadow: none; }
        [data-bs-theme='dark'] .glass { background: #292929; }
        .nav-link { border-left: 4px solid transparent; padding: .7rem .8rem; }
        .nav-link:hover, .nav-link:focus { background: var(--metro-blue); border-left-color: #50e6ff; }
        .card, .modal-content { border-radius: 0; border: 0; box-shadow: 0 1px 2px rgba(0, 0, 0, .16); }
        .card-header { background: var(--metro-teal); border: 0; color: #fff; font-weight: 600; }
        .btn { border-radius: 0; }
        .btn-primary { background: var(--metro-blue); border-color: var(--metro-blue); }
        .btn-danger { background: var(--metro-orange); border-color: var(--metro-orange); }
        .btn-success { background: #107c10; border-color: #107c10; }
        .btn-outline-primary { color: var(--metro-blue); border-color: var(--metro-blue); }
        .form-control, .form-select { border-radius: 0; border-color: #a6a6a6; }
        .form-control:focus, .form-select:focus { border-color: var(--metro-blue); box-shadow: 0 0 0 .15rem rgba(0, 120, 212, .2); }
        .table > :not(caption) > * > * { padding: .8rem; }
        .table thead th { background: #e5f1fb; color: #1b1b1b; font-weight: 600; }
        [data-bs-theme='dark'] .table thead th { background: #333; color: #fff; }
        .alert { border: 0; border-left: 5px solid currentColor; border-radius: 0; }
        .page-heading { align-items: end; display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .page-heading h1 { font-size: 1.75rem; margin: 0; }
        .eyebrow { color: var(--metro-teal); font-size: .75rem; font-weight: 700; letter-spacing: .08em; margin: 0 0 .25rem; text-transform: uppercase; }
        @media (max-width: 767px) { .sidebar { width: 72px; padding: .75rem !important; } .sidebar h4, .sidebar p, .sidebar .nav-link { font-size: 0; } .sidebar .nav-link { min-height: 38px; } .main-panel { padding: 1rem; } .page-heading { align-items: start; flex-direction: column; } }
    </style>
</head>
<body x-init="$watch('darkMode', value => localStorage.setItem('dark_mode', value ? '1' : '0'))">
<div class="d-flex">
    <aside class="sidebar text-white p-3">
        <div class="d-flex align-items-center gap-2 mb-3"><img src="{{ asset('images/devcraft-labs-logo.svg') }}" width="46" height="46" alt="DevCraft Labs"><div><h4 class="fw-bold mb-0">DevCraft Labs</h4><p class="small text-info mb-0">CPanel Deployment Manager</p></div></div>
        <nav class="nav flex-column gap-2">
            @can('dashboard.view')<a class="nav-link text-white" href="{{ route('dashboard') }}">Dashboard</a>@endcan
            @can('scripts.view')<a class="nav-link text-white" href="{{ route('deployment-scripts.index') }}">Deployment Scripts</a>@endcan
            @can('deployments.view')<a class="nav-link text-white" href="{{ route('deployments.queue') }}">Deployments</a>@endcan
            @can('deployments.view')<a class="nav-link text-white" href="{{ route('monitoring.index') }}">Server Monitoring</a>@endcan
            @can('cron.view')<a class="nav-link text-white" href="{{ route('cron.index') }}">Cron Manager</a>@endcan
            @can('connections.view')<a class="nav-link text-white" href="{{ route('redis-profiles.index') }}">Redis Manager</a><a class="nav-link text-white" href="{{ route('smtp-profiles.index') }}">SMTP Manager</a><a class="nav-link text-white" href="{{ route('telegram-connections.index') }}">Telegram Manager</a>@endcan
            @can('provisioning.view')<a class="nav-link text-white" href="{{ route('provisioning.index') }}">DB Provisioning</a>@endcan
            @can('settings.view')<a class="nav-link text-white" href="{{ route('settings.index') }}">Settings</a>@endcan
            @can('users.manage')<a class="nav-link text-white" href="{{ route('users.index') }}">User Management</a>@endcan
        </nav>
    </aside>
    <main class="main-panel flex-grow-1 p-4">
        <div class="glass p-3 mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ auth()->user()->name ?? 'Guest' }}</h5>
                <small class="text-secondary">{{ now()->format('d M Y H:i') }}</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" @click="darkMode = !darkMode">Toggle Theme</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
