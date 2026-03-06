<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        :root {
            --bg: #f4f1ea;
            --ink: #1f2a2e;
            --brand: #0d6b6f;
            --brand-dark: #084f52;
            --card: #ffffff;
            --muted: #5d6d72;
            --line: #d8ddd6;
            --danger: #9f1c1c;
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

        .nav-links a {
            text-decoration: none;
            color: var(--ink);
        }

        .nav-cta {
            text-decoration: none;
            color: #fff;
            background: var(--brand);
            padding: 0.55rem 0.9rem;
            border: 0;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .auth-wrapper {
            min-height: calc(100vh - 160px);
            display: grid;
            place-items: center;
            padding: 3rem 0;
        }

        .auth-card {
            width: min(560px, 95vw);
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1.4rem;
        }

        h1 {
            margin: 0 0 0.35rem;
            font-size: 1.6rem;
        }

        .subtitle {
            margin: 0 0 1.4rem;
            color: var(--muted);
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 600;
            font-size: 0.92rem;
        }

        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 0.65rem 0.75rem;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .btn {
            text-decoration: none;
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-weight: 600;
            border: 0;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-secondary {
            color: var(--ink);
            background: #fff;
            border: 1px solid var(--line);
        }

        .errors {
            background: #fff1f1;
            border: 1px solid #f6d0d0;
            color: var(--danger);
            border-radius: 10px;
            padding: 0.8rem;
            margin-bottom: 1rem;
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

        .footer-links a {
            text-decoration: none;
            color: var(--ink);
        }

        @media (max-width: 800px) {
            .nav-links { display: none; }
            .grid-2 { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>
    @include('partials.navigation')

    <main class="auth-wrapper">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
