@extends('layouts.app')
@section('content')
<h4 class="mb-3">Create SMTP Profile</h4>
<form method="POST" action="{{ route('smtp-profiles.store') }}" class="card card-body">@csrf @include('smtp.form')<button class="btn btn-primary">Save</button></form>
@endsection
