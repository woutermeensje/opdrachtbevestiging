@extends('layouts.app', [
    'title' => 'Opdrachtbevestiging '.$confirmation->reference,
    'metaDescription' => 'Opdrachtbevestiging bekijken en akkoord bevestigen.',
    'metaRobots' => 'noindex,nofollow,noarchive',
    'canonical' => route('confirmations.public.show', $confirmation->public_token),
])

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
            @include('partials.confirmations.public-document', ['confirmation' => $confirmation])

            <aside class="card public-sign-card">
                <h2>Akkoord bevestigen</h2>

                @if (session('status'))
                    <div class="dashboard-notice">{{ session('status') }}</div>
                @endif

                @if ($confirmation->status === 'ingetrokken')
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
                            <span>Ik ga akkoord met de inhoud van deze opdrachtbevestiging{{ $confirmation->terms_path ? ' en de bijgevoegde algemene voorwaarden' : '' }}.</span>
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
