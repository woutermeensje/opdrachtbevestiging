@extends('layouts.app', [
    'title' => 'Opdrachtbevestiging '.$confirmation->reference,
    'metaDescription' => 'Opdrachtbevestiging bekijken en akkoord bevestigen.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('confirmations.public.show', $confirmation->public_token),
])

@php
    $senderAddressLines = $confirmation->senderAddressLines();
    $logo = $confirmation->senderCompanyLogoDataUri();
@endphp

@section('content')
    <section class="page-hero">
        <div class="container">
            <p class="page-eyebrow">Opdrachtbevestiging</p>
            <h1>{{ $confirmation->title }}</h1>
            <p class="page-intro">Bekijk hieronder de opdrachtbevestiging en bevestig desgewenst je akkoord.</p>
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
                    @if ($logo !== null)
                        <img class="public-document-logo" src="{{ $logo }}" alt="">
                    @endif
                    <span class="dashboard-status dashboard-status-{{ $confirmation->status }}">{{ ucfirst($confirmation->status) }}</span>
                </div>

                <div class="public-document-grid">
                    <div>
                        <p><strong>Opdrachtnemer</strong></p>
                        <p>{{ $confirmation->senderCompanyDisplayName() }}</p>
                        @foreach ($senderAddressLines as $line)
                            <p>{{ $line }}</p>
                        @endforeach
                        @if (filled($confirmation->sender_kvk_number))
                            <p>KVK: {{ $confirmation->sender_kvk_number }}</p>
                        @endif
                    </div>
                    <div>
                        <p><strong>Opdrachtgever</strong></p>
                        <p>{{ $confirmation->client_name }}</p>
                        <p>{{ $confirmation->client_contact_name ?: '-' }}</p>
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
                    <div class="dashboard-rich-content">{!! $confirmation->descriptionHtml() !!}</div>
                </div>

                @if ($confirmation->defaultAgreementsHtml() !== '')
                    <div class="public-document-body">
                        <h3>Basis afspraken</h3>
                        <div class="dashboard-rich-content">{!! $confirmation->defaultAgreementsHtml() !!}</div>
                    </div>
                @endif

                @if ($confirmation->terms_path)
                    <div class="public-document-body">
                        <h3>Algemene voorwaarden</h3>
                        <p>{{ $confirmation->terms_original_name ?: basename($confirmation->terms_path) }} is meegestuurd als bijlage.</p>
                    </div>
                @endif
            </article>

            <aside class="card public-sign-card">
                <h2>Akkoord bevestigen</h2>

                @if (session('status'))
                    <div class="dashboard-notice">{{ session('status') }}</div>
                @endif

                @if ($confirmation->status === 'getekend')
                    <p>Deze opdrachtbevestiging is akkoord bevestigd door <strong>{{ $confirmation->signer_name }}</strong>.</p>
                    <p><strong>Akkoorddatum:</strong> {{ optional($confirmation->signed_at)->format('d-m-Y H:i') }}</p>
                @elseif ($confirmation->status === 'ingetrokken')
                    <p>Deze opdrachtbevestiging is ingetrokken. Je kunt deze niet meer akkoord bevestigen.</p>
                @elseif ($confirmation->status === 'verzonden')
                    @include('partials.forms.errors')

                    <form method="POST" action="{{ route('confirmations.public.accept', $confirmation->public_token) }}">
                        @csrf

                        <label for="signer_name">Jouw naam</label>
                        <input id="signer_name" name="signer_name" type="text" value="{{ old('signer_name', $confirmation->client_name) }}" required>

                        <label class="checkbox-field" for="accept_terms">
                            <input id="accept_terms" name="accept_terms" type="checkbox" value="1" required>
                            <span>Ik ga akkoord met de inhoud van deze opdrachtbevestiging.</span>
                        </label>

                        <button type="submit" class="btn btn-primary">Akkoord bevestigen</button>
                    </form>
                @else
                    <p>Deze opdrachtbevestiging kan niet akkoord worden bevestigd.</p>
                @endif
            </aside>
        </div>
    </section>
@endsection
