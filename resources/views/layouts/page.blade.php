@extends('layouts.app', [
    'title' => $title ?? config('app.name'),
])

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="page-eyebrow">{{ $eyebrow ?? config('app.name') }}</p>
            <h1>{{ $heading }}</h1>
            @isset($intro)
                <p class="page-intro">{{ $intro }}</p>
            @endisset
        </div>
    </section>

    <section class="page-content">
        <div class="container page-content-grid">
            @yield('page-content')
        </div>
    </section>
@endsection
