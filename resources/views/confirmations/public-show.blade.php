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
                        <p><strong>Vergoeding</strong></p>
                        <p>EUR {{ number_format((float) $confirmation->total_value, 2, ',', '.') }} ({{ $confirmation->valueVatLabel() }})</p>
                    </div>
                    <div>
                        <p><strong>Startdatum</strong></p>
                        <p>{{ optional($confirmation->agreement_date)->format('d-m-Y') ?? 'Niet ingevuld' }}</p>
                    </div>
                </div>

                @if (filled($confirmation->duration))
                    <div class="public-document-grid">
                        <div>
                            <p><strong>Duur van de opdracht</strong></p>
                            <p>{{ $confirmation->duration }}</p>
                        </div>
                    </div>
                @endif

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

                @if (filled($confirmation->termination_terms))
                    <div class="public-document-body">
                        <h3>Beëindiging van de opdracht</h3>
                        <p>{{ $confirmation->termination_terms }}</p>
                    </div>
                @endif

                @if ($confirmation->terms_path)
                    <div class="public-document-body">
                        <h3>Algemene voorwaarden</h3>
                        <p>{{ $confirmation->terms_original_name ?: basename($confirmation->terms_path) }} is meegestuurd als bijlage en is van toepassing op deze opdracht.</p>
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

                        <div class="signature-field" data-signature-pad>
                            <label for="signer_signature_canvas">Handtekening</label>
                            <canvas id="signer_signature_canvas" class="signature-canvas" width="640" height="220" data-signature-canvas></canvas>
                            <input type="hidden" name="signer_signature_data" value="{{ old('signer_signature_data') }}" data-signature-input>
                            <div class="signature-actions">
                                <button type="button" class="btn btn-secondary" data-signature-clear>Wissen</button>
                            </div>
                        </div>

                        <label class="checkbox-field" for="accept_terms">
                            <input id="accept_terms" name="accept_terms" type="checkbox" value="1" required>
                            <span>Ik ga akkoord met de inhoud van deze opdrachtbevestiging@if ($confirmation->terms_path) en de bijgevoegde algemene voorwaarden@endif.</span>
                        </label>

                        <button type="submit" class="btn btn-primary">Akkoord geven</button>
                    </form>
                @else
                    <p>Deze opdrachtbevestiging kan niet akkoord worden bevestigd.</p>
                @endif
            </aside>
        </div>
    </section>

    <script>
        document.querySelectorAll('[data-signature-pad]').forEach((pad) => {
            const canvas = pad.querySelector('[data-signature-canvas]');
            const input = pad.querySelector('[data-signature-input]');
            const clearButton = pad.querySelector('[data-signature-clear]');
            const form = pad.closest('form');

            if (!canvas || !input || !form) {
                return;
            }

            const context = canvas.getContext('2d');
            let isDrawing = false;
            let hasSignature = false;

            const resizeCanvas = () => {
                const ratio = window.devicePixelRatio || 1;
                const bounds = canvas.getBoundingClientRect();
                const previousSignature = hasSignature ? canvas.toDataURL('image/png') : null;

                canvas.width = Math.max(1, Math.floor(bounds.width * ratio));
                canvas.height = Math.max(1, Math.floor(bounds.height * ratio));
                context.setTransform(ratio, 0, 0, ratio, 0, 0);
                context.lineCap = 'round';
                context.lineJoin = 'round';
                context.lineWidth = 2;
                context.strokeStyle = '#333333';

                if (previousSignature) {
                    const image = new Image();
                    image.onload = () => {
                        context.drawImage(image, 0, 0, bounds.width, bounds.height);
                    };
                    image.src = previousSignature;
                }
            };

            const point = (event) => {
                const rect = canvas.getBoundingClientRect();

                return {
                    x: event.clientX - rect.left,
                    y: event.clientY - rect.top,
                };
            };

            canvas.addEventListener('pointerdown', (event) => {
                event.preventDefault();
                canvas.setPointerCapture(event.pointerId);
                isDrawing = true;
                hasSignature = true;

                const start = point(event);
                context.beginPath();
                context.moveTo(start.x, start.y);
            });

            canvas.addEventListener('pointermove', (event) => {
                if (!isDrawing) {
                    return;
                }

                event.preventDefault();
                const next = point(event);
                context.lineTo(next.x, next.y);
                context.stroke();
            });

            const stopDrawing = () => {
                if (!isDrawing) {
                    return;
                }

                isDrawing = false;
                input.value = canvas.toDataURL('image/png');
            };

            canvas.addEventListener('pointerup', stopDrawing);
            canvas.addEventListener('pointercancel', stopDrawing);
            canvas.addEventListener('pointerleave', stopDrawing);

            clearButton?.addEventListener('click', () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
                input.value = '';
                hasSignature = false;
                canvas.classList.remove('is-invalid');
            });

            form.addEventListener('submit', (event) => {
                if (hasSignature) {
                    input.value = canvas.toDataURL('image/png');
                    return;
                }

                event.preventDefault();
                canvas.classList.add('is-invalid');
                canvas.focus();
            });

            canvas.setAttribute('tabindex', '0');
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);
        });
    </script>
@endsection
