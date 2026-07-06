@extends('layouts.app', [
    'title' => 'Inloggen | Opdrachtbevestiging.nl',
    'metaDescription' => 'Log in op Opdrachtbevestiging.nl om opdrachtbevestigingen aan te maken, te versturen en te beheren.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('login'),
    'mainClass' => 'auth-wrapper',
])

@section('content')
    @include('partials.auth.login-form')
@endsection
