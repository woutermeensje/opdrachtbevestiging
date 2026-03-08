<section id="features" class="container grid">
    @include('partials.cards.card', [
        'title' => 'Template bibliotheek',
        'text' => 'Gebruik slimme templates per dienst, klanttype of branche.',
    ])

    @include('partials.cards.card', [
        'id' => 'werkwijze',
        'title' => 'Digitale akkoordflow',
        'text' => 'Stuur direct ter ondertekening en houd realtime status bij.',
    ])

    @include('partials.cards.card', [
        'title' => 'Volledige historie',
        'text' => 'Alle versies, wijzigingen en akkoordmomenten op 1 plek.',
    ])
</section>
