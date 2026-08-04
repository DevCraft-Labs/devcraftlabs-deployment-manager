@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit SMTP Profile</h4>
<form method="POST" action="{{ route('smtp-profiles.update', $profile) }}" class="card card-body">@csrf @method('PUT') @include('smtp.form', ['profile' => $profile])<button class="btn btn-primary">Update</button></form>
@endsection
