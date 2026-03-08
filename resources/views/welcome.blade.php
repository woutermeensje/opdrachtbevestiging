@extends('layouts.app', [
    'title' => config('app.name'),
])

@section('content')
    @include('partials.home.hero')
    @include('partials.home.feature-grid')
    @include('partials.home.workspace-grid')
    @include('partials.home.equal-grid')
@endsection
