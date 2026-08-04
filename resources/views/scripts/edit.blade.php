@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Deployment Script</h4>
<form method="POST" action="{{ route('deployment-scripts.update', $script) }}" class="card card-body">@csrf @method('PUT') @include('scripts.form', ['script' => $script])<button class="btn btn-primary">Update Script</button></form>
@endsection
