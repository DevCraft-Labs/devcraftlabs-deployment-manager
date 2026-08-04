@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit MySQL Provisioning Connection</h4>
<form method="POST" action="{{ route('provisioning.update', $connection) }}" class="card card-body">@csrf @method('PUT') @include('provisioning.form', ['connection' => $connection])<button class="btn btn-primary">Update Connection</button></form>
@endsection
