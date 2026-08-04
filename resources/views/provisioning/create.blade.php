@extends('layouts.app')
@section('content')
<h4 class="mb-3">Add MySQL Provisioning Connection</h4>
<form method="POST" action="{{ route('provisioning.store') }}" class="card card-body">@csrf @include('provisioning.form')<button class="btn btn-primary">Save Connection</button></form>
@endsection
