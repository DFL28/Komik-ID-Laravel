<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Scraper\KomikcastScraper;
use Illuminate\Support\Facades\Http;

$scraper = new KomikcastScraper();

// Test with a specific manga URL
$url = 'https://komikcast.co.id/manga/eleceed/'; 
echo "Testing Manga Detail Fetch: $url\n";

$response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$html = $response->body();

echo "HTML Length: " . strlen($html) . "\n";

// NEW REGEX from KomikcastScraper.php
$regex = '/<a[^>]*href="([^"]+)"[^>]*>\s*(?:<b>)?\s*Chapter\s*(\d+(?:\.\d+)?)/i';
preg_match_all($regex, $html, $matches, PREG_SET_ORDER);
echo "New Regex Matches: " . count($matches) . "\n";

if (count($matches) > 0) {
    echo "First 5 matches:\n";
    for ($i = 0; $i < min(5, count($matches)); $i++) {
        echo " - Chapter " . $matches[$i][2] . " -> " . $matches[$i][1] . "\n";
    }
}
