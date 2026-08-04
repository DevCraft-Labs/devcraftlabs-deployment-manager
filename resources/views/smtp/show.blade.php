@extends('layouts.app')
@section('content')
<div class="card card-body"><h4>{{ $profile->name }}</h4><p>{{ $profile->description }}</p></div>
@endsection
