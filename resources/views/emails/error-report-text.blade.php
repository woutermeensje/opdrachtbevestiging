Er is een fout opgetreden op {{ config('app.name') }}.

Tijdstip:   {{ $occurredAt }}
Request:    {{ $url }}
Gebruiker:  {{ $user ?? 'niet ingelogd' }}

Fout:       {{ $exceptionClass }}
Melding:    {{ $exceptionMessage }}
Locatie:    {{ $location }}

Stacktrace:
{{ $trace }}

Dezelfde fout wordt hooguit één keer per 15 minuten gemaild. Zie de serverlogs voor alle meldingen.
