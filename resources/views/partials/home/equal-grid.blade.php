<section class="container equal-grid">
    @include('partials.cards.card', [
        'class' => 'card-image',
        'image' => [
            'src' => asset('Logo/Logo-opdrachtbevesting.png'),
            'alt' => 'Logo Opdrachtbevestiging.nl',
        ],
    ])

    @include('partials.cards.card', [
        'title' => 'Planning en acties',
        'text' => 'Gebruik dit blok voor deadlines, vervolgstappen en interne opvolging.',
    ])
</section>
