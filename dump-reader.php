<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://komikcast.co.id/eleceed-chapter-366/'; 
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Referer' => 'https://komikcast.co.id/'
])->get($url);

$html = $response->body();

if (preg_match('/id="readerarea"[^>]*>(.*?)<\/div>/is', $html, $match)) {
    echo "READER AREA CONTENT:\n";
    echo $match[1];
} else {
    echo "Reader area not found via Regex. Dumping part of body:\n";
    echo substr($html, 0, 2000);
}
