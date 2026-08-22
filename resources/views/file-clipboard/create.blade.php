@extends('layouts.public')

@section('title', 'Temporary File Clipboard')

@section('content')
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
@endsection
