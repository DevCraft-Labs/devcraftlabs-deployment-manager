<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | DevCraft Labs</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/devcraft-labs-logo.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --metro-blue: #0078d4; --metro-teal: #008272; }
        body { background: #f3f3f3 !important; color: #1b1b1b !important; font-family: "Segoe UI", Tahoma, sans-serif; }
        .login-panel { border: 0; border-top: 8px solid var(--metro-teal); border-radius: 0; box-shadow: 0 1px 4px rgba(0, 0, 0, .25); }
        .btn, .form-control { border-radius: 0; }
        .btn-primary { background: var(--metro-blue); border-color: var(--metro-blue); }
        .form-control:focus { border-color: var(--metro-blue); box-shadow: 0 0 0 .15rem rgba(0, 120, 212, .2); }
    </style>
</head>
<body class="bg-dark text-light d-flex align-items-center" style="min-height:100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-4">
            <div class="card login-panel">
                <div class="card-body p-4">
                    <div class="text-center mb-3"><img src="{{ asset('images/devcraft-labs-logo.svg') }}" width="110" height="110" alt="DevCraft Labs"><h4 class="mt-2 mb-1">DevCraft Labs</h4><p class="text-muted mb-0">CPanel Deployment Manager</p></div>
                    <form method="POST" action="{{ route('login.attempt') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input class="form-control" name="username" value="{{ old('username') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input class="form-control" type="password" name="password" required>
                        </div>
                        <button class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
