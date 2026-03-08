<nav class="site-nav">
    <div class="container nav-inner">

        {{-- Logo (links) --}}
        <a href="{{ url('/') }}" class="brand">
            <img src="/logo/Logo-opdrachtbevesting.png" alt="Opdrachtbevestiging.nl" class="nav-logo">
        </a>

        {{-- Menu links (midden, desktop) --}}
        <ul class="nav-links">
            <li><a href="{{ url('/#hoe-het-werkt') }}">Hoe het werkt</a></li>
            <li><a href="{{ url('/#prijzen') }}">Prijzen</a></li>
            <li><a href="{{ url('/#contact') }}">Contact</a></li>
        </ul>

        {{-- Buttons (rechts, desktop) --}}
        <div class="nav-actions">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Uitloggen</button>
                </form>
            @else
                <span class="nav-separator"></span>
                <a href="{{ route('login') }}" class="btn btn-secondary">Inloggen</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Gratis account</a>
            @endauth
        </div>

        {{-- Hamburger (mobiel) --}}
        <button class="nav-hamburger" id="nav-hamburger" aria-label="Menu openen">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</nav>

{{-- Mobile menu --}}
<div class="mobile-menu" id="mobile-menu">
    <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Menu sluiten">✕</button>

    <ul class="mobile-nav-links">
        <li><a href="{{ url('/#hoe-het-werkt') }}">Hoe het werkt</a></li>
        <li><a href="{{ url('/#prijzen') }}">Prijzen</a></li>
        <li><a href="{{ url('/#contact') }}">Contact</a></li>
    </ul>

    <div class="mobile-nav-actions">
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary" style="width:100%">Uitloggen</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn btn-secondary">Inloggen</a>
            <a href="{{ route('register') }}" class="btn btn-primary">Gratis account</a>
        @endauth
    </div>
</div>

<div class="mobile-menu-backdrop" id="mobile-menu-backdrop"></div>

<script>
    (function () {
        const hamburger = document.getElementById('nav-hamburger');
        const menu = document.getElementById('mobile-menu');
        const backdrop = document.getElementById('mobile-menu-backdrop');
        const closeBtn = document.getElementById('mobile-menu-close');

        function openMenu() {
            menu.classList.add('is-open');
            backdrop.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            menu.classList.remove('is-open');
            backdrop.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        hamburger.addEventListener('click', openMenu);
        closeBtn.addEventListener('click', closeMenu);
        backdrop.addEventListener('click', closeMenu);
    })();
</script>
