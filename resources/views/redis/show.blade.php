@extends('layouts.app')

@section('content')
<div class="card card-body">
    <h4>{{ $profile->name }}</h4>
    <p>{{ $profile->description }}</p>
    <p><strong>Host:</strong> {{ $profile->host }}:{{ $profile->port }}</p>
</div>
@endsection
