@extends('layouts.dashboard', [
    'title' => 'Aanmaken',
])

@section('content')
    @include('partials.dashboard.page-header', [
        'eyebrow' => 'Aanmaken',
        'title' => 'Nieuwe opdrachtbevestiging',
        'text' => 'Hier komt de werkomgeving voor het opstellen van een nieuwe opdrachtbevestiging.',
    ])

    @include('partials.dashboard.panel', [
        'title' => 'Voorbereiding',
        'slot' => '
            <p>De volgende stap is hier een formulier of wizard te plaatsen waarmee je klantgegevens, diensten, tarieven en voorwaarden kunt invoeren.</p>
        ',
    ])
@endsection
