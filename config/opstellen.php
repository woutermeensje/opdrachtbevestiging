<?php

/*
|--------------------------------------------------------------------------
| Opstellen-pagina's
|--------------------------------------------------------------------------
| Eén regel per documenttype. De slug is ook de naam van de view in
| resources/views/opstellen/ en het pad op /opstellen/{slug}. Deze paden
| zijn gelijk aan de oude WordPress-URL's; niet wijzigen zonder redirect.
*/

return [
    'types' => [
        'engagement-letter' => 'Engagement letter',
        'inkooporder' => 'Inkooporder',
        'opdrachtbevestiging' => 'Opdrachtbevestiging',
        'opdrachtbon' => 'Opdrachtbon',
        'orderbevestiging' => 'Orderbevestiging',
        'plaatsingsbevestiging' => 'Plaatsingsbevestiging',
        'projectbevestiging' => 'Projectbevestiging',
    ],
];
