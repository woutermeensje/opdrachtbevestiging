<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bewaartermijn
    |--------------------------------------------------------------------------
    | Activiteiten ouder dan dit aantal dagen worden dagelijks opgeruimd.
    */
    'retention_days' => (int) env('ACTIVITY_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Gelogde acties
    |--------------------------------------------------------------------------
    | Routenaam => omschrijving. Alleen geslaagde requests worden vastgelegd.
    | Inloggen, uitloggen en registreren worden via auth-events gelogd.
    */
    'actions' => [
        'verification.send' => 'Bevestigingsmail opnieuw aangevraagd',

        'billing.checkout' => 'Betaling abonnement gestart',

        'dashboard.profile.account.update' => 'Accountgegevens gewijzigd',
        'dashboard.profile.password.update' => 'Wachtwoord gewijzigd',
        'dashboard.profile.account.destroy' => 'Account verwijderd',
        'dashboard.profile.company.update' => 'Bedrijfsgegevens gewijzigd',
        'dashboard.profile.documents.update' => 'Documentinstellingen gewijzigd',
        'dashboard.profile.agreements.update' => 'Basisafspraken gewijzigd',
        'dashboard.profile.brand.update' => 'Logo & huisstijl gewijzigd',

        'dashboard.contacts.store' => 'Opdrachtgever toegevoegd',
        'dashboard.contacts.update' => 'Opdrachtgever gewijzigd',

        'dashboard.create.store' => 'Opdrachtbevestiging aangemaakt',
        'dashboard.create.draft' => 'Concept opgeslagen',
        'dashboard.create.draft.discard' => 'Concept verwijderd',
        'dashboard.confirmations.send' => 'Opdrachtbevestiging verzonden',
        'dashboard.confirmations.retract' => 'Opdrachtbevestiging ingetrokken',
        'dashboard.confirmations.pdf' => 'PDF opdrachtbevestiging gedownload',
        'confirmations.public.accept' => 'Opdrachtbevestiging getekend door opdrachtgever',

        'dashboard.ai-assist.text' => 'AI: tekst verbeterd',
        'dashboard.ai-assist.concept' => 'AI: concept gegenereerd',
        'dashboard.ai-assist.check' => 'AI: volledigheidscheck',

        'dashboard.quotes.create.store' => 'Offerte aangemaakt',
        'dashboard.quotes.send' => 'Offerte verzonden',
        'dashboard.quotes.accept' => 'Offerte gemarkeerd als geaccepteerd',
        'dashboard.quotes.reject' => 'Offerte gemarkeerd als afgewezen',
        'dashboard.quotes.pdf' => 'PDF offerte gedownload',
    ],

];
