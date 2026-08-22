<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | DevCraft Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --metro-blue: #0078d4; --metro-teal: #008272; }
        body { background: #f3f3f3; color: #1b1b1b; font-family: "Segoe UI", Tahoma, sans-serif; }
        .public-panel { border: 0; border-top: 8px solid var(--metro-teal); border-radius: 0; box-shadow: 0 1px 4px rgba(0, 0, 0, .18); }
        .btn, .form-control { border-radius: 0; }
        .btn-primary { background: var(--metro-blue); border-color: var(--metro-blue); }
        .form-control:focus { border-color: var(--metro-blue); box-shadow: 0 0 0 .15rem rgba(0, 120, 212, .2); }
        .alert { border: 0; border-left: 5px solid currentColor; border-radius: 0; }
    </style>
</head>
<body>
<main class="container py-5" style="max-width: 850px;">
    <div class="card public-panel"><div class="card-body p-4">@yield('content')</div></div>
</main>
@stack('scripts')
</body>
</html>