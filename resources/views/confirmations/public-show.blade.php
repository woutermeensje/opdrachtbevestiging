@extends('layouts.app', [
    'title' => 'Opdrachtbevestiging '.$confirmation->reference,
    'metaDescription' => 'Beveiligde opdrachtbevestiging bekijken en digitaal bevestigen.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('confirmations.public.show', $confirmation->public_token),
])

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="page-eyebrow">Opdrachtbevestiging</p>
            <h1>{{ $confirmation->title }}</h1>
            <p class="page-intro">Bekijk hieronder de opdrachtbevestiging en bevestig deze digitaal in de browser.</p>
        </div>
    </section>

    <section class="page-content">
        <div class="container public-confirmation-layout">
            <article class="card public-document">
                <div class="public-document-header">
                    <div>
                        <p class="page-eyebrow">Referentie</p>
                        <h2>{{ $confirmation->reference }}</h2>
                    </div>
                    <span class="dashboard-status dashboard-status-{{ $confirmation->status }}">{{ ucfirst($confirmation->status) }}</span>
                </div>

                <div class="public-document-grid">
                    <div>
                        <p><strong>Opdrachtgever</strong></p>
                        <p>{{ $confirmation->user->company_name }}</p>
                    </div>
                    <div>
                        <p><strong>Klant</strong></p>
                        <p>{{ $confirmation->client_name }}</p>
                        <p>{{ $confirmation->client_email }}</p>
                    </div>
                </div>

                <div class="public-document-grid">
                    <div>
                        <p><strong>Waarde</strong></p>
                        <p>EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p><strong>Opdrachtdatum</strong></p>
                        <p>{{ optional($confirmation->agreement_date)->format('d-m-Y') ?? 'Niet ingevuld' }}</p>
                    </div>
                </div>

                <div class="public-document-body">
                    <h3>Omschrijving</h3>
                    <p>{{ $confirmation->description ?: 'Er is geen aanvullende omschrijving toegevoegd.' }}</p>
                </div>
            </article>

            <aside class="card public-sign-card">
                <h2>Digitaal bevestigen</h2>

                @if (session('status'))
                    <div class="dashboard-notice">{{ session('status') }}</div>
                @endif

                @if ($confirmation->status === 'getekend')
                    <p>Deze opdrachtbevestiging is ondertekend door <strong>{{ $confirmation->signer_name }}</strong>.</p>
                    <p><strong>Tekendatum:</strong> {{ optional($confirmation->signed_at)->format('d-m-Y H:i') }}</p>
                @else
                    @include('partials.forms.errors')

                    <form method="POST" action="{{ route('confirmations.public.sign', $confirmation->public_token) }}">
                        @csrf

                        <label for="signer_name">Jouw naam</label>
                        <input id="signer_name" name="signer_name" type="text" value="{{ old('signer_name', $confirmation->client_name) }}" required>

                        <label class="checkbox-field" for="accept_terms">
                            <input id="accept_terms" name="accept_terms" type="checkbox" value="1" required>
                            <span>Ik ga akkoord met de inhoud van deze opdrachtbevestiging en bevestig dit digitaal.</span>
                        </label>

                        <button type="submit" class="btn btn-primary">Ondertekenen en bevestigen</button>
                    </form>
                @endif
            </aside>
        </div>
    </section>
@endsection
