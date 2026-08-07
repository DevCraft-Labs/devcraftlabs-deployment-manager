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
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div><h1 class="h3 mb-1">Temporary Clipboard</h1><p class="text-secondary mb-0">Expires in <strong id="expiry">{{ $entry['expires_in'] }}</strong> seconds. Updating restarts the five-minute lifetime.</p></div>
                <a class="btn btn-outline-secondary" href="{{ route('clipboard.create') }}">New clipboard</a>
            </div>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('clipboard.update', $identifier) }}">
                @csrf
                @method('PUT')
                <label for="content" class="form-label">Clipboard content</label>
                <textarea id="content" name="content" class="form-control font-monospace" rows="14" maxlength="100000" required>{{ old('content', $entry['content']) }}</textarea>
                <div class="d-flex gap-2 mt-3"><button class="btn btn-primary">Save and restart timer</button><button class="btn btn-outline-secondary" type="button" id="copy">Copy content</button></div>
            </form>
            <form method="POST" action="{{ route('clipboard.destroy', $identifier) }}" class="mt-3" onsubmit="return confirm('Delete this clipboard now?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-link text-danger p-0">Delete clipboard</button>
            </form>
        </div>
    </div>
</main>
<script>
const expiry = document.getElementById('expiry');
const timer = setInterval(() => { const seconds = Math.max(0, Number(expiry.textContent) - 1); expiry.textContent = seconds; if (seconds === 0) { clearInterval(timer); window.location.reload(); } }, 1000);
document.getElementById('copy').addEventListener('click', async () => { await navigator.clipboard.writeText(document.getElementById('content').value); document.getElementById('copy').textContent = 'Copied'; });
</script>
</body>
</html>
