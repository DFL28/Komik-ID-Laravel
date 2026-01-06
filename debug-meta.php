<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

// URL Sample
$url = 'https://komikcast.co.id/manga/boruto-two-blue-vortex/'; 
echo "Testing Fetch: $url\n";

$response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$html = $response->body();

// 1. Test Sinopsis
echo "\n--- SINOPSIS CHECK ---\n";
// Pattern yang ada sekarang
$patterns = [
    '/<div[^>]*class="[^"]*sinopsis[^"]*"[^>]*>(.*?)<\/div>/is',
    '/<div[^>]*class="[^"]*entry-content[^"]-entry[^"]*"[^>]*>(.*?)<\/div>/is',
    '/<div[^>]*itemprop="description"[^>]*>(.*?)<\/div>/is'
];

$found = false;
foreach ($patterns as $p) {
    if (preg_match($p, $html, $match)) {
        echo "MATCH FOUND with pattern: $p\n";
        echo "Length: " . strlen(strip_tags($match[1])) . "\n";
        echo "Snippet: " . substr(strip_tags($match[1]), 0, 100) . "...\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "FAILED to find synopsis. Dumping relevant HTML part:\n";
    // Cari area sekitar kata "Sinopsis"
    if (preg_match('/Sinopsis.*?<p>(.*?)<\/p>/is', $html, $context)) {
        echo "Context found: " . $context[1] . "\n";
    }
}

// 2. Test Chapter Count
echo "\n--- CHAPTER CHECK ---\n";
if (preg_match_all(
    '/<li[^>]*data-num="(\d+(?:\.\d+)?)"[^>]*>.*?<a[^>]*href="([^"]+)"[^>]*>/is',
    $html,
    $matches
)) {
    echo "Found " . count($matches[1]) . " chapters via data-num.\n";
    echo "First (Newest): Check " . $matches[1][0] . "\n";
    echo "Last (Oldest): Check " . end($matches[1]) . "\n";
} else {
    echo "NO CHAPTERS FOUND via data-num regex.\n";
}
