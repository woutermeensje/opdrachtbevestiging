@extends('layouts.app')

@section('content')
    <section class="auth-card">
        <h1>Dashboard</h1>
        <p class="subtitle">
            Je bent ingelogd als <strong>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong>
            bij <strong>{{ auth()->user()->company_name }}</strong>.
        </p>

        <p>Vanaf hier kunnen we nu je eerste onderdelen voor opdrachtbevestigingen gaan bouwen.</p>
    </section>
@endsection
