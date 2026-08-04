@extends('layouts.app')

@section('content')
<h4 class="mb-3">Create Redis Profile</h4>
<form method="POST" action="{{ route('redis-profiles.store') }}" class="card card-body">
    @csrf
    @include('redis.form')
    <button class="btn btn-primary">Save</button>
</form>
@endsection
