@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit User: {{ $managedUser->username }}</h4>
<form method="POST" action="{{ route('users.update', $managedUser) }}" class="card card-body">@csrf @method('PUT') @include('users.form', ['managedUser' => $managedUser])<button class="btn btn-primary">Save User</button></form>
@endsection
