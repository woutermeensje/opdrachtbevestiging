<?php

return [
    'base_url' => env('KVK_API_BASE_URL', 'https://api.kvk.nl/test/api/v1'),
    'search_base_url' => env('KVK_SEARCH_API_BASE_URL', 'https://api.kvk.nl/test/api/v2'),
    'api_key' => env('KVK_API_KEY'),
    'timeout' => (int) env('KVK_API_TIMEOUT', 10),
];
