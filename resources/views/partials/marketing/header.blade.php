@php
    $navItems = [
        ['label' => 'FAQ', 'url' => url('/veelgestelde-vragen')],
        ['label' => 'Contact', 'url' => route('pages.contact')],
        ['label' => 'Wat het kost', 'url' => route('pages.tariffs')],
    ];
@endphp

<header class="mk-header" role="banner">
    <div class="mk-header__inner">
        <div class="mk-header__brand">
            <a href="{{ route('home') }}">
                <span class="mk-header__logo">Opdrachtbevestiging.nl</span>
                <img src="{{ asset('images/logo-icon.png') }}" alt="Opdrachtbevestiging.nl" class="mk-header__favicon" width="36" height="36">
            </a>
        </div>

        <nav class="mk-header__nav" aria-label="Primaire navigatie">
            <ul class="mk-nav__list">
                @foreach ($navItems as $item)
                    <li class="mk-nav__item">
                        <a href="{{ $item['url'] }}" @class(['mk-nav__link', 'is-active' => url()->current() === $item['url']])>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mk-header__cta">
            @auth
                <a href="{{ route('dashboard') }}" class="mk-btn mk-btn--primary">Naar dashboard</a>
            @else
                <a href="{{ route('login') }}" class="mk-btn mk-btn--outline">Inloggen</a>
                <a href="{{ route('register') }}" class="mk-btn mk-btn--primary">Aanmelden</a>
            @endauth
        </div>

        <button type="button" class="mk-header__hamburger" aria-label="Menu openen" aria-expanded="false" aria-controls="mk-mobile-nav">
            <span class="mk-hamburger__bar"></span>
            <span class="mk-hamburger__bar"></span>
            <span class="mk-hamburger__bar"></span>
        </button>
    </div>
</header>

<div id="mk-mobile-nav" class="mk-mobile-nav" aria-hidden="true">
    <div class="mk-mobile-nav__panel">
        <button type="button" class="mk-mobile-nav__close" aria-label="Menu sluiten">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <ul class="mk-mobile-nav__list">
            @foreach ($navItems as $item)
                <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endforeach
        </ul>

        <div class="mk-mobile-nav__divider"></div>

        <div class="mk-mobile-nav__ctas">
            @auth
                <a href="{{ route('dashboard') }}" class="mk-btn mk-btn--primary">Naar dashboard</a>
            @else
                <a href="{{ route('login') }}" class="mk-btn mk-btn--outline">Inloggen</a>
                <a href="{{ route('register') }}" class="mk-btn mk-btn--primary">Aanmelden</a>
            @endauth
        </div>
    </div>
</div>

<script>
(function () {
    const hamburger = document.querySelector('.mk-header__hamburger');
    const mobileNav = document.getElementById('mk-mobile-nav');
    const closeBtn = mobileNav && mobileNav.querySelector('.mk-mobile-nav__close');
    if (!hamburger || !mobileNav) return;

    const setOpen = (open) => {
        mobileNav.classList.toggle('is-open', open);
        hamburger.classList.toggle('is-open', open);
        hamburger.setAttribute('aria-expanded', String(open));
        mobileNav.setAttribute('aria-hidden', String(!open));
        document.body.style.overflow = open ? 'hidden' : '';
    };

    hamburger.addEventListener('click', () => setOpen(!mobileNav.classList.contains('is-open')));
    closeBtn && closeBtn.addEventListener('click', () => setOpen(false));
    mobileNav.addEventListener('click', (e) => {
        if (!e.target.closest('.mk-mobile-nav__panel')) setOpen(false);
    });
    mobileNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) setOpen(false);
    });
})();
</script>
