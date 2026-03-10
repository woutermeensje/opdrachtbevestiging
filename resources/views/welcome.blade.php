@extends('layouts.app', [
    'title' => config('app.name'),
])

@section('content')
    @include('partials.home.hero')
    @include('partials.home.content-block')
@endsection
