<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        :root {
            --bg: #f4f1ea;
            --ink: #1f2a2e;
            --brand: #0d6b6f;
            --brand-dark: #084f52;
            --card: #ffffff;
            --muted: #5d6d72;
            --line: #d8ddd6;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background: radial-gradient(circle at top right, #dcefea 0%, var(--bg) 45%);
            color: var(--ink);
        }

        .container {
            width: min(1080px, 92vw);
            margin: 0 auto;
        }

        .site-nav {
            position: sticky;
            top: 0;
            background: rgba(244, 241, 234, 0.95);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid var(--line);
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 0;
        }

        .brand {
            font-weight: 700;
            color: var(--ink);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-links a,
        .footer-links a {
            text-decoration: none;
            color: var(--ink);
        }

        .nav-cta {
            text-decoration: none;
            color: #fff;
            background: var(--brand);
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            font-weight: 600;
        }

        .hero {
            padding: 5rem 0 3rem;
            text-align: center;
        }

        .hero h1 {
            font-size: clamp(1.8rem, 4vw, 3rem);
            margin: 0 0 1rem;
        }

        .hero p {
            color: var(--muted);
            max-width: 650px;
            margin: 0 auto 1.5rem;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            gap: 0.8rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-secondary {
            border: 1px solid var(--line);
            color: var(--ink);
            background: #fff;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            padding-bottom: 4rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1.2rem;
        }

        .card h3 {
            margin-top: 0;
        }

        .site-footer {
            border-top: 1px solid var(--line);
            background: #fff;
            padding: 1.1rem 0;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .footer-inner p {
            margin: 0;
            color: var(--muted);
        }

        .footer-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        @media (max-width: 800px) {
            .nav-links { display: none; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
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
                    <a href="#" class="btn btn-primary">Gratis proberen</a>
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
