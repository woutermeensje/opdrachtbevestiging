@extends('layouts.dashboard', [
    'title' => $confirmation->title,
])

@php
    $statusClass = \Illuminate\Support\Str::slug($confirmation->status);
    $statusLabel = ucfirst(str_replace('-', ' ', $confirmation->status));
    $documentPreviewUrl = $confirmation->hasPdf()
        ? route('dashboard.confirmations.pdf.preview', $confirmation).'#toolbar=0&navpanes=0'
        : null;
    $extraDocuments = collect([
        $confirmation->terms_path ? 'Algemene voorwaarden' : null,
        $confirmation->attachment_path ? 'Bijlage' : null,
        $confirmation->quote_path ? 'Offerte' : null,
    ])->filter()->values();
    $specificationCount = collect($confirmation->filledSpecificationSections())
        ->sum(fn (array $section): int => count($section['fields']));
    $canSend = ! in_array($confirmation->status, ['getekend', 'ingetrokken'], true);
@endphp

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Opdrachtbevestiging',
        'title' => $confirmation->title,
        'text' => 'Referentie '.$confirmation->reference.' voor '.$confirmation->client_name.'.',
    ])

    @if (session('status'))
        <div class="dashboard-notice">{{ session('status') }}</div>
    @endif

    <div class="confirmation-builder-shell confirmation-detail-shell">
        <div class="confirmation-builder-stage">
            <article class="confirmation-document-preview confirmation-pdf-document-preview">
                @if ($documentPreviewUrl !== null)
                    <iframe
                        class="confirmation-pdf-frame"
                        src="{{ $documentPreviewUrl }}"
                        title="PDF opdrachtbevestiging {{ $confirmation->reference }}"
                    ></iframe>
                @else
                    <div class="confirmation-pdf-empty">
                        @include('partials.icons.icon', ['name' => 'file-text', 'size' => 34])
                        <h2>PDF nog niet gegenereerd</h2>
                        <p>Verstuur de opdrachtbevestiging om de definitieve PDF aan te maken.</p>
                    </div>
                @endif
            </article>
        </div>

        <aside class="confirmation-builder-aside confirmation-detail-aside">
            <div class="confirmation-builder-status-card confirmation-detail-card">
                <span class="dashboard-status dashboard-status-{{ $statusClass }}">{{ $statusLabel }}</span>

                <dl>
                    <div>
                        <dt>Opdrachtgever</dt>
                        <dd>
                            {{ $confirmation->client_name }}
                            <span>{{ $confirmation->client_contact_name ?: $confirmation->client_email }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt>Referentie</dt>
                        <dd>{{ $confirmation->reference }}</dd>
                    </div>
                    <div>
                        <dt>Verzonden</dt>
                        <dd>{{ $confirmation->sent_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Bekeken</dt>
                        <dd>{{ $confirmation->viewed_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Akkoord</dt>
                        <dd>
                            {{ $confirmation->signed_at?->format('d-m-Y H:i') ?? '-' }}
                            @if (filled($confirmation->signer_name))
                                <span>{{ $confirmation->signer_name }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Vervaldatum</dt>
                        <dd>{{ $confirmation->expires_at?->format('d-m-Y') ?? '-' }}</dd>
                    </div>
                    @if ($confirmation->agreement_date !== null)
                        <div>
                            <dt>Startdatum</dt>
                            <dd>{{ $confirmation->agreement_date->format('d-m-Y') }}</dd>
                        </div>
                    @endif
                    @if ((float) $confirmation->total_value > 0)
                        <div>
                            <dt>Vergoeding</dt>
                            <dd>EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }} <span>{{ $confirmation->valueVatLabel() }}</span></dd>
                        </div>
                    @endif
                    <div>
                        <dt>Specificaties</dt>
                        <dd>{{ $specificationCount > 0 ? $specificationCount.' ingevuld' : 'Geen extra specificaties' }}</dd>
                    </div>
                    <div>
                        <dt>Bijlagen</dt>
                        <dd>{{ $extraDocuments->isNotEmpty() ? $extraDocuments->implode(', ') : 'Geen extra bijlagen' }}</dd>
                    </div>
                </dl>

                <div class="confirmation-builder-submit-actions confirmation-detail-actions">
                    @if ($confirmation->hasPdf())
                        <a href="{{ route('dashboard.confirmations.pdf', $confirmation) }}" class="btn btn-secondary">
                            @include('partials.icons.icon', ['name' => 'file-text', 'size' => 16])
                            Download PDF
                        </a>
                    @endif

                    @if (filled($confirmation->public_token))
                        <a href="{{ $confirmation->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            Online openen
                        </a>
                    @endif

                    @if ($canSend)
                        <form method="POST" action="{{ route('dashboard.confirmations.send', $confirmation) }}" class="dashboard-action-form">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                {{ $confirmation->status === 'verzonden' ? 'Nogmaals versturen' : 'Per e-mail versturen' }}
                            </button>
                        </form>
                    @endif

                    @if ($confirmation->canBeRetracted())
                        <form
                            method="POST"
                            action="{{ route('dashboard.confirmations.retract', $confirmation) }}"
                            class="dashboard-action-form"
                            onsubmit="return confirm('Weet je zeker dat je deze opdrachtbevestiging wilt intrekken?');"
                        >
                            @csrf
                            <button type="submit" class="btn btn-danger">Intrekken</button>
                        </form>
                    @endif
                </div>
            </div>
        </aside>
    </div>
@endsection
