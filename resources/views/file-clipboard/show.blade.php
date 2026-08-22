@extends('layouts.public')

@section('title', 'Temporary File Clipboard')

@section('content')
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div><h1 class="h3 mb-1">Temporary File Clipboard</h1><p class="text-secondary mb-0">Expires in <strong id="expiry">{{ $entry['expires_in'] }}</strong> seconds.</p></div>
                <a class="btn btn-outline-secondary" href="{{ route('file-clipboard.create') }}">Upload another</a>
            </div>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="border rounded p-3 mb-3">
                <div class="fw-semibold text-break">{{ $entry['original_name'] }}</div>
                <div class="text-secondary small">{{ number_format($entry['size'] / 1048576, 2) }} MB &middot; {{ $entry['mime_type'] }}</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-primary" href="{{ route('file-clipboard.download', $identifier) }}">Download</a>
            </div>
            <form method="POST" action="{{ route('file-clipboard.destroy', $identifier) }}" class="mt-3" onsubmit="return confirm('Delete this file now?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-link text-danger p-0">Delete file</button>
            </form>
@endsection
@push('scripts')
<script>
const expiry = document.getElementById('expiry');
const timer = setInterval(() => { const seconds = Math.max(0, Number(expiry.textContent) - 1); expiry.textContent = seconds; if (seconds === 0) { clearInterval(timer); window.location.reload(); } }, 1000);
</script>
@endpush
