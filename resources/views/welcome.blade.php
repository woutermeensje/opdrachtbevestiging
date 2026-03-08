<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.navigation')

    <main>
        <section class="hero" id="start">
            <div class="container">
                <h1>Sneller professionele opdrachtbevestigingen versturen</h1>
                <p>
                    Opdrachtbevestiging.nl helpt je om heldere, juridisch nette opdrachtbevestigingen te maken,
                    goed te keuren en direct te verzenden vanuit een centrale workflow.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary">Gratis proberen</a>
                    <a href="#features" class="btn btn-secondary">Bekijk features</a>
                </div>
            </div>
        </section>

        <section id="features" class="container grid">
            <article class="card">
                <h3>Template bibliotheek</h3>
                <p>Gebruik slimme templates per dienst, klanttype of branche.</p>
            </article>
            <article class="card" id="werkwijze">
                <h3>Digitale akkoordflow</h3>
                <p>Stuur direct ter ondertekening en houd realtime status bij.</p>
            </article>
            <article class="card">
                <h3>Volledige historie</h3>
                <p>Alle versies, wijzigingen en akkoordmomenten op 1 plek.</p>
            </article>
        </section>
    </main>

    @include('partials.footer')
</body>
</html>
