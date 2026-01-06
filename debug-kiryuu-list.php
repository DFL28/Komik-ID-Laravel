<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'https://kiryuu03.com';
$url = $baseUrl . '/manga/';

echo "Fetching: $url\n";

$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
])->get($url);

echo "Status: " . $response->status() . "\n";
$html = $response->body();
echo "Length: " . strlen($html) . "\n";

// Final Regex Check
preg_match_all('/<a[^>]*href="(https:\/\/kiryuu03\.com\/manga\/[^"]+)"[^>]*>.*?<img[^>]*src="([^"]+)"[^>]*>/is', $html, $matches);

echo "Found " . count($matches[0]) . " items via FINAL Regex.\n";
if (count($matches[0]) > 0) {
    echo "First Image: " . $matches[2][0] . "\n";
}

if (count($matches[0]) == 0) {
    echo "Dumping first 500 chars of HTML:\n";
    echo substr($html, 0, 500) . "\n...\n";
    
    // Generic search for manga links
    if (preg_match_all('/<a[^>]*href="https:\/\/kiryuu03\.com\/manga\/([^"]+)"[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
        echo "Found " . count($matches[0]) . " links with /manga/. Dumping context of first match:\n";
        
        $offset = $matches[0][0][1];
        $contextStart = max(0, $offset - 200);
        $contextLength = 600;
        
        echo "Context:\n" . htmlspecialchars(substr($html, $contextStart, $contextLength)) . "\n";
        
        // Try to construct a regex based on this
        // Looking for title and image
    } else {
        echo "No /manga/ links found even with generic search. Is page empty?\n";
    }
} else {
    print_r($matches[1]);
}
