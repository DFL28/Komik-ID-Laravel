<?php

return [
    'delay_ms' => env('SCRAPER_DELAY_MS', 500),
    'concurrency' => env('SCRAPER_CONCURRENCY', 5),
    'timeout' => env('SCRAPER_TIMEOUT', 30),
    'source_pages' => env('SCRAPER_SOURCE_PAGES', 0),
    
    'sources' => [
        'komikindo' => [
            'name' => 'Komikindo',
            'url' => 'https://komikindo.ch',
            'enabled' => true,
        ],
        'komiku' => [
            'name' => 'Komiku',
            'url' => 'https://komiku.id',
            'enabled' => false,
        ],
        'westmanga' => [
            'name' => 'WestManga',
            'url' => 'https://westmanga.info',
            'enabled' => false,
        ],
        'maid' => [
            'name' => 'Maid',
            'url' => 'https://maid.my.id',
            'enabled' => false,
        ],
    ],
];
