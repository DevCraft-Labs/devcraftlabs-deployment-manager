<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temporary Clipboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 850px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h1 class="h3">Temporary Clipboard</h1>
            <p class="text-secondary">Create a shareable clipboard that automatically expires after 5 minutes. Anyone with its private link can read, change, or delete it.</p>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('clipboard.store') }}">
                @csrf
                <label for="content" class="form-label">Clipboard content</label>
                <textarea id="content" name="content" class="form-control font-monospace" rows="14" maxlength="100000" required>{{ old('content') }}</textarea>
                <div class="form-text">Maximum 100,000 characters. Data is stored only in Redis and is not saved to the database.</div>
                <button class="btn btn-primary mt-3">Create temporary clipboard</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
