@extends('layouts.app')
@section('content')
<div class="card card-body"><h4>{{ $connection->name }}</h4><p>{{ $connection->description }}</p></div>
@endsection
