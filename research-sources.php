<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Fetching Kiryuu Homepage...\n";
$html = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
])->get('https://kiryuu02.com/')->body();

echo "Length: " . strlen($html) . "\n";

// Dump all links found
preg_match_all('/<a[^>]+href="([^"]+)"/i', $html, $matches);
$links = array_unique($matches[1]);
$mangaLinks = array_filter($links, fn($l) => strpos($l, '/manga/') !== false);

echo "Found " . count($links) . " total links.\n";
echo "Found " . count($mangaLinks) . " manga links.\n";

print_r(array_slice($mangaLinks, 0, 5));

// Try one if found
if (!empty($mangaLinks)) {
    $target = reset($mangaLinks);
    echo "\nTesting: $target\n";
    $res = Http::get($target);
    echo "Status: " . $res->status() . "\n";
    if (str_contains($res->body(), 'chapter')) {
        echo "Contains 'chapter'.\n";
    }
}
