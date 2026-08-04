@extends('layouts.app')

@section('content')
<h4 class="mb-3">Edit Redis Profile</h4>
<form method="POST" action="{{ route('redis-profiles.update', $profile) }}" class="card card-body">
    @csrf @method('PUT')
    @include('redis.form', ['profile' => $profile])
    <button class="btn btn-primary">Update</button>
</form>
@endsection
