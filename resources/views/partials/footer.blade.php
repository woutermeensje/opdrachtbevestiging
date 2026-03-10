<footer class="site-footer">
    <div class="container footer-main">
        <div class="footer-brand">
            <a href="{{ url('/') }}" class="footer-logo">Opdrachtbevestiging.nl</a>
            <p>
                Professioneel opdrachtbevestigingen opstellen, verzenden en opvolgen vanuit
                een centrale workflow.
            </p>
        </div>

        <div class="footer-column">
            <h3>Pagina's</h3>
            <div class="footer-links">
                <a href="{{ route('pages.how-it-works') }}">Hoe het werkt</a>
                <a href="{{ route('pages.what-is-confirmation') }}">Wat is een opdrachtbevestiging?</a>
                <a href="{{ route('pages.create-confirmation') }}">Opdrachtbevestiging opstellen</a>
                <a href="{{ route('pages.pricing') }}">Prijzen</a>
                <a href="{{ route('pages.contact') }}">Contact</a>
            </div>
        </div>

        <div class="footer-column">
            <h3>Contact</h3>
            <div class="footer-links">
                <a href="mailto:info@opdrachtbevestiging.nl">info@opdrachtbevestiging.nl</a>
                <span>Binnenkort beschikbaar: demo via Calendly</span>
                <span>Reactie op vragen binnen twee werkdagen</span>
            </div>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; {{ now()->year }} Opdrachtbevestiging.nl. Alle rechten voorbehouden.</p>
        <p class="footer-note">Privacy en voorwaarden kunnen hier later als aparte pagina's worden toegevoegd.</p>
    </div>
</footer>
