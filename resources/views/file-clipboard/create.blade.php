<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Temporary File Clipboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 850px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h1 class="h3">Temporary File Clipboard</h1>
            <p class="text-secondary">Upload a file to get a private, shareable link that automatically expires after 5 minutes. Anyone with the link can download or delete it.</p>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('file-clipboard.store') }}" enctype="multipart/form-data">
                @csrf
                <label for="file" class="form-label">Choose a file</label>
                <input id="file" name="file" type="file" class="form-control" required>
                <div class="form-text">Maximum {{ number_format($maxFileSizeKb / 1024, 1) }} MB. Expires 5 minutes after upload.</div>
                <button class="btn btn-primary mt-3">Upload file</button>
            </form>
            <p class="text-secondary mt-3 mb-0"><a href="{{ route('clipboard.create') }}">Need to share text instead?</a></p>
        </div>
    </div>
</main>
</body>
</html>
