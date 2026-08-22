@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Project Configuration</p>
        <h1>{{ $script->name }}</h1>
        <p class="text-secondary mb-0">Edit the project's <code>.env</code> file.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('deployment-scripts.show', $script) }}">Back to Script</a>
</div>

<form method="POST" action="{{ route('deployment-scripts.environment.update', $script) }}">
    @csrf
    @method('PUT')
    <div class="card metro-card">
        <div class="card-header">.env</div>
        <div class="card-body">
            <textarea class="form-control font-monospace" name="contents" rows="24" spellcheck="false" aria-label="Environment file contents">{{ old('contents', $contents) }}</textarea>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('deployment-scripts.show', $script) }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Environment</button>
        </div>
    </div>
</form>
@endsection