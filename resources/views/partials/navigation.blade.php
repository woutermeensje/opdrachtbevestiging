<nav class="site-nav">
    <div class="container nav-inner">
        <a href="{{ url('/') }}" class="brand">Opdrachtbevestiging.nl</a>

        <ul class="nav-links">
            <li><a href="{{ url('/#features') }}">Features</a></li>
            <li><a href="{{ url('/#werkwijze') }}">Werkwijze</a></li>
            <li><a href="{{ url('/#contact') }}">Contact</a></li>
        </ul>

        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-cta">Uitloggen</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="nav-cta">Inloggen</a>
        @endauth
    </div>
</nav>
