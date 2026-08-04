@extends('layouts.app')
@section('content')
<h4 class="mb-3">Create User</h4>
<form method="POST" action="{{ route('users.store') }}" class="card card-body">@csrf @include('users.form')<button class="btn btn-primary">Create User</button></form>
@endsection
