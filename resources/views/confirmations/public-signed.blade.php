@extends('layouts.app', [
    'title' => 'Opdrachtbevestiging '.$confirmation->reference.' - getekend',
    'metaDescription' => 'Opdrachtbevestiging getekend.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('confirmations.public.signed', $confirmation->public_token),
])

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="page-eyebrow">Opdrachtbevestiging</p>
            <h1>Akkoord bevestigd</h1>
            <p class="page-intro">Bedankt {{ $confirmation->signer_name }}, je akkoord op deze opdrachtbevestiging is vastgelegd.</p>
        </div>
    </section>

    <section class="page-content">
        <div class="container public-confirmation-layout">
            @include('partials.confirmations.public-document', ['confirmation' => $confirmation])

            <aside class="card public-sign-card">
                <h2>Akkoord bevestigd</h2>

                @if (session('status'))
                    <div class="dashboard-notice">{{ session('status') }}</div>
                @endif

                <p>Deze opdrachtbevestiging is akkoord bevestigd door <strong>{{ $confirmation->signer_name }}</strong>.</p>
                <p><strong>Akkoorddatum:</strong> {{ optional($confirmation->signed_at)->format('d-m-Y H:i') }}</p>

                @if ($confirmation->hasPdf())
                    <p>
                        <a href="{{ route('confirmations.public.pdf', $confirmation->public_token) }}" class="btn btn-primary">Download PDF</a>
                    </p>
                @endif

                <div class="public-account-cta">
                    <p><strong>Account aanmaken</strong></p>
                    <p>Account aanmaken om jouw huidige en toekomstige opdrachtbevestigingen te beheren.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary">Account aanmaken</a>
                </div>
            </aside>
        </div>
    </section>
@endsection
