<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://komikcast.co.id/manga/eleceed/'; 
$response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$html = $response->body();

$regex = '/<a[^>]*href="([^"]+)"[^>]*>.*?Chapter\s*(\d+(?:\.\d+)?)/is';
preg_match_all($regex, $html, $matches, PREG_SET_ORDER);
echo "Count: " . count($matches) . "\n";
if (count($matches) > 0) {
    echo "First: " . $matches[0][2] . " -> " . $matches[0][1] . "\n";
}
