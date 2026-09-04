<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('dark_mode') === '1', menuOpen: false }" x-bind:data-bs-theme="darkMode ? 'dark' : 'light'">
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
        [x-cloak] { display: none !important; }
        .app-rail { align-items: center; background: var(--metro-charcoal); bottom: 0; display: flex; flex: 0 0 104px; flex-direction: column; left: 0; min-height: 100vh; padding: 1rem .75rem; position: fixed; top: 0; z-index: 1040; }
        .app-rail .brand-mark { height: 46px; width: 46px; }
        .metro-menu-button { align-items: center; background: var(--metro-blue); border: 0; color: #fff; display: flex; flex-direction: column; font-size: .85rem; font-weight: 600; gap: .4rem; height: 92px; justify-content: center; margin: auto 0; width: 80px; }
        .metro-menu-button svg { height: 28px; width: 28px; }
        .metro-start-menu { background: var(--metro-paper); bottom: 0; left: 104px; overflow-y: auto; padding: 2rem; position: fixed; right: 0; top: 0; z-index: 1050; }
        [data-bs-theme='dark'] .metro-start-menu { background: #161616; }
        .metro-start-menu__header { align-items: start; display: flex; justify-content: space-between; margin-bottom: 2rem; }
        .metro-start-menu__title { font-size: 2rem; font-weight: 300; margin: 0; }
        .metro-tiles { display: grid; gap: .5rem; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); max-width: 960px; }
        .metro-tile { color: #fff; display: flex; flex-direction: column; justify-content: end; min-height: 128px; padding: 1rem; text-decoration: none; transition: filter .15s ease, transform .15s ease; }
        .metro-tile:hover, .metro-tile:focus { color: #fff; filter: brightness(.88); outline: 3px solid #50e6ff; outline-offset: -3px; transform: translateY(-2px); }
        .metro-tile--blue { background: #0078d4; }.metro-tile--teal { background: #008272; }.metro-tile--orange { background: #d83b01; }.metro-tile--green { background: #107c10; }.metro-tile--magenta { background: #b146c2; }.metro-tile--slate { background: #4c4a48; }
        .metro-tile__icon { height: 30px; margin-bottom: auto; stroke-width: 1.75; width: 30px; }
        .metro-tile__label { font-size: 1.05rem; font-weight: 600; }.metro-tile__hint { font-size: .78rem; opacity: .85; }
        .main-panel { margin-left: 104px; min-height: 100vh; padding: 2rem; width: calc(100% - 104px); }
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
        .main-panel { min-width: 0; overflow-x: hidden; }
        .table-responsive + .pagination, .pagination { flex-wrap: wrap; gap: .25rem; margin-bottom: 0; }
        .pagination > * { flex: 0 0 auto; }
        .pagination .page-link { min-width: 2.5rem; overflow: hidden; text-align: center; text-overflow: ellipsis; white-space: nowrap; }
        .pagination-nav { max-width: 100%; overflow-x: auto; padding-bottom: .25rem; }
        .announcement-card { border-left: 5px solid #c50f1f; }
        .announcement-card .card-header { background: #c50f1f; }
        .announcement-card .announcement-content { background: #fde7e9; color: #4a0d13; }
        [data-bs-theme='dark'] .announcement-card .announcement-content { background: #4a0d13; color: #f9d7db; }
        .announcement-content > :last-child { margin-bottom: 0; }
        .page-heading { align-items: end; display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .page-heading h1 { font-size: 1.75rem; margin: 0; }
        .eyebrow { color: var(--metro-teal); font-size: .75rem; font-weight: 700; letter-spacing: .08em; margin: 0 0 .25rem; text-transform: uppercase; }
        @media (max-width: 767px) { .app-rail { flex-basis: 72px; padding: .75rem .25rem; } .app-rail .brand-mark { height: 38px; width: 38px; } .metro-menu-button { height: 74px; width: 62px; } .metro-start-menu { left: 72px; padding: 1.25rem; } .metro-start-menu__title { font-size: 1.6rem; } .metro-tiles { grid-template-columns: 1fr; } .main-panel { margin-left: 72px; padding: 1rem; width: calc(100% - 72px); } .page-heading { align-items: start; flex-direction: column; } .pagination { gap: .15rem; } .pagination .page-link { min-width: 2.25rem; padding: .375rem .55rem; } }
    </style>
</head>
<body x-init="$watch('darkMode', value => localStorage.setItem('dark_mode', value ? '1' : '0'))">
<div class="d-flex">
    <aside class="app-rail text-white" aria-label="Application menu">
        <img class="brand-mark" src="{{ asset('images/devcraft-labs-logo.svg') }}" alt="DevCraft Labs">
        <button class="metro-menu-button" type="button" @click="menuOpen = true" aria-controls="metro-start-menu" :aria-expanded="menuOpen.toString()"><svg viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path fill="currentColor" d="M4 7.5 21.5 5v18H4V7.5Zm20.5-2.8L44 2v21H24.5V4.7ZM4 25h17.5v18L4 40.5V25Zm20.5 0H44v21l-19.5-2.7V25Z"/></svg>Menu</button>
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
<section id="metro-start-menu" class="metro-start-menu" x-cloak x-show="menuOpen" x-transition.opacity @keydown.escape.window="menuOpen = false" aria-label="Application menu">
    <div class="metro-start-menu__header"><div><p class="eyebrow">DevCraft Labs</p><h2 class="metro-start-menu__title">Menu</h2></div><button class="btn btn-outline-secondary" type="button" @click="menuOpen = false">Close</button></div>
    <nav class="metro-tiles">
        @can('dashboard.view')<a class="metro-tile metro-tile--blue" href="{{ route('dashboard') }}"><i class="metro-tile__icon" data-lucide="layout-dashboard" aria-hidden="true"></i><span class="metro-tile__label">Dashboard</span><span class="metro-tile__hint">Overview and activity</span></a>@endcan
        @can('scripts.view')<a class="metro-tile metro-tile--teal" href="{{ route('deployment-scripts.index') }}"><i class="metro-tile__icon" data-lucide="rocket" aria-hidden="true"></i><span class="metro-tile__label">Deployment &amp; Monitoring</span><span class="metro-tile__hint">Scripts and service health</span></a>@endcan
        @can('deployments.view')<a class="metro-tile metro-tile--orange" href="{{ route('deployments.queue') }}"><i class="metro-tile__icon" data-lucide="list-checks" aria-hidden="true"></i><span class="metro-tile__label">Deployments</span><span class="metro-tile__hint">Execution queue</span></a>@endcan
        @can('cron.view')<a class="metro-tile metro-tile--green" href="{{ route('cron.index') }}"><i class="metro-tile__icon" data-lucide="calendar-clock" aria-hidden="true"></i><span class="metro-tile__label">Cron Manager</span><span class="metro-tile__hint">Schedules</span></a>@endcan
        @can('connections.view')<a class="metro-tile metro-tile--magenta" href="{{ route('redis-profiles.index') }}"><i class="metro-tile__icon" data-lucide="database" aria-hidden="true"></i><span class="metro-tile__label">Redis Manager</span><span class="metro-tile__hint">Redis profiles</span></a><a class="metro-tile metro-tile--blue" href="{{ route('smtp-profiles.index') }}"><i class="metro-tile__icon" data-lucide="mail" aria-hidden="true"></i><span class="metro-tile__label">SMTP Manager</span><span class="metro-tile__hint">SMTP profiles</span></a><a class="metro-tile metro-tile--teal" href="{{ route('telegram-connections.index') }}"><i class="metro-tile__icon" data-lucide="send" aria-hidden="true"></i><span class="metro-tile__label">Telegram Manager</span><span class="metro-tile__hint">Telegram connections</span></a>@endcan
        @can('provisioning.view')<a class="metro-tile metro-tile--slate" href="{{ route('provisioning.index') }}"><i class="metro-tile__icon" data-lucide="database-zap" aria-hidden="true"></i><span class="metro-tile__label">DB Provisioning</span><span class="metro-tile__hint">Explore application data</span></a>@endcan
        @can('settings.view')<a class="metro-tile metro-tile--orange" href="{{ route('settings.index') }}"><i class="metro-tile__icon" data-lucide="settings" aria-hidden="true"></i><span class="metro-tile__label">Settings</span><span class="metro-tile__hint">Application configuration</span></a>@endcan
        @can('users.manage')<a class="metro-tile metro-tile--green" href="{{ route('users.index') }}"><i class="metro-tile__icon" data-lucide="users" aria-hidden="true"></i><span class="metro-tile__label">User Management</span><span class="metro-tile__hint">Roles and access</span></a>@endcan
    </nav>
</section>
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
@stack('scripts')
</body>
</html>
