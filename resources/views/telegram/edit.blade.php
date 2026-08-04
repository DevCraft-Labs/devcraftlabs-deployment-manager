@extends('layouts.app')
@section('content')
<h4 class="mb-3">Edit Telegram Connection</h4>
<form method="POST" action="{{ route('telegram-connections.update', $connection) }}" class="card card-body">@csrf @method('PUT') @include('telegram.form', ['connection' => $connection])<button class="btn btn-primary">Update</button></form>
@endsection
