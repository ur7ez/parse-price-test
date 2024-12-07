<?php

return [
    'methods' => [
        'selenium' => \App\Services\SeleniumService::class,
        'http' => \App\Services\HttpService::class,
    ],
    'default_method' => 'http',

    'http' => [
        'user_agent' => env('PARSER_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.5790.110 Safari/537.36'),
    ],

    'selenium' => [
        'timeout' => 7,
        'driver_pollibng_interval' => 200,
    ],

    'placeholders' => [
        'invalid_url' => 'URL Error',
        'price_not_found' => 'Price not found',
        'wrong_format' => 'Invalid format',
    ],
];
