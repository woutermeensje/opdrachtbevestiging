<?php

return [
    'base_url' => env('KVK_API_BASE_URL', 'https://api.kvk.nl/test/api/v1'),
    'search_base_url' => env('KVK_SEARCH_API_BASE_URL', 'https://api.kvk.nl/test/api/v2'),
    'api_key' => env('KVK_API_KEY', 'l7xx1f2691f2520d487b902f4e0b57a0b197'),
    'timeout' => (int) env('KVK_API_TIMEOUT', 10),
];
