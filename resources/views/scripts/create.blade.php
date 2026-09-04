@extends('layouts.app')
@section('content')
@include('scripts.breadcrumbs', ['breadcrumbItems' => [['label' => 'Create Script']]])
<h4 class="mb-3">Create Deployment Script</h4>
<form method="POST" action="{{ route('deployment-scripts.store') }}" class="card card-body">@csrf @include('scripts.form')<button class="btn btn-primary">Save Script</button></form>
@endsection
