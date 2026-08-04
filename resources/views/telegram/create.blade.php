@extends('layouts.app')
@section('content')
<h4 class="mb-3">Create Telegram Connection</h4>
<form method="POST" action="{{ route('telegram-connections.store') }}" class="card card-body">@csrf @include('telegram.form')<button class="btn btn-primary">Save</button></form>
@endsection
