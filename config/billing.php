<?php

return [
    'mollie_key' => env('MOLLIE_KEY'),

    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 14),

    'currency' => env('BILLING_CURRENCY', 'EUR'),

    'vat_rate' => (float) env('BILLING_VAT_RATE', 21),

    'plans' => [
        'monthly' => [
            'name' => 'Maandelijks',
            'amount_ex_vat' => env('BILLING_MONTHLY_AMOUNT_EX_VAT', '19.95'),
            'interval' => '1 month',
            'period' => 'month',
            'description' => 'Maandelijks abonnement Opdrachtbevestiging.nl',
            'cta' => 'Kies maandelijks',
        ],

        'yearly' => [
            'name' => 'Jaarlijks',
            'amount_ex_vat' => env('BILLING_YEARLY_AMOUNT_EX_VAT', '199.00'),
            'interval' => '12 months',
            'period' => 'year',
            'description' => 'Jaarabonnement Opdrachtbevestiging.nl',
            'cta' => 'Kies jaarlijks',
        ],
    ],
];
