<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('dark_mode') === '1' }" x-bind:data-bs-theme="darkMode ? 'dark' : 'light'">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DevCraft Labs CPanel Deployment Manager') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: radial-gradient(circle at 10% 20%, #102a43 0%, #243b53 35%, #f0f4f8 35%); min-height: 100vh; }
        [data-bs-theme='dark'] body { background: radial-gradient(circle at 10% 20%, #0b1020 0%, #1c2540 35%, #0f172a 35%); }
        .sidebar { width: 280px; min-height: 100vh; }
        .main-panel { min-height: 100vh; }
        .glass { backdrop-filter: blur(8px); background: rgba(255,255,255,0.85); }
        [data-bs-theme='dark'] .glass { background: rgba(15,23,42,0.85); }
    </style>
</head>
<body x-init="$watch('darkMode', value => localStorage.setItem('dark_mode', value ? '1' : '0'))">
<div class="d-flex">
    <aside class="sidebar text-white p-3" style="background:#102a43;">
        <h4 class="fw-bold">{{ config('app.name', 'Laravel') }}</h4>
        <p class="small text-info">CPanel Deployment Manager</p>
        <nav class="nav flex-column gap-2">
            @can('dashboard.view')<a class="nav-link text-white" href="{{ route('dashboard') }}">Dashboard</a>@endcan
            @can('scripts.view')<a class="nav-link text-white" href="{{ route('deployment-scripts.index') }}">Deployment Scripts</a>@endcan
            @can('deployments.view')<a class="nav-link text-white" href="{{ route('deployments.queue') }}">Deployment Queue</a>@endcan
            @can('cron.view')<a class="nav-link text-white" href="{{ route('cron.index') }}">Cron Manager</a>@endcan
            @can('connections.view')<a class="nav-link text-white" href="{{ route('redis-profiles.index') }}">Redis Manager</a><a class="nav-link text-white" href="{{ route('smtp-profiles.index') }}">SMTP Manager</a><a class="nav-link text-white" href="{{ route('telegram-connections.index') }}">Telegram Manager</a>@endcan
            @can('provisioning.view')<a class="nav-link text-white" href="{{ route('provisioning.index') }}">DB Provisioning</a>@endcan
            @can('settings.view')<a class="nav-link text-white" href="{{ route('settings.index') }}">Settings</a>@endcan
            @can('users.manage')<a class="nav-link text-white" href="{{ route('users.index') }}">User Management</a>@endcan
        </nav>
    </aside>
    <main class="main-panel flex-grow-1 p-4">
        <div class="glass rounded-4 shadow p-3 mb-4 d-flex justify-content-between align-items-center">
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
