@extends('layouts.app')
@section('content')
@include('scripts.breadcrumbs', ['breadcrumbItems' => [['label' => $script->name, 'url' => route('deployment-scripts.show', $script)], ['label' => 'Edit']]])
<h4 class="mb-3">Edit Deployment Script</h4>
<form method="POST" action="{{ route('deployment-scripts.update', $script) }}" class="card card-body">@csrf @method('PUT') @include('scripts.form', ['script' => $script])<button class="btn btn-primary">Update Script</button></form>
@endsection
