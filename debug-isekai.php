<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

// Tebakan URL
$url = 'https://komikcast.co.id/manga/isekai-meikyuu-de-harem-wo/'; // Coba pakai 'wo' atau 'o'
echo "Fetching: $url\n";

$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
])->get($url);

if ($response->status() == 404) {
    // Retry alternative URL
    $url = 'https://komikcast.co.id/manga/isekai-meikyuu-de-harem-o/';
    echo "404. Retrying: $url\n";
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ])->get($url);
}

if (!$response->successful()) {
    die("Failed to fetch: " . $response->status());
}

$html = $response->body();
file_put_contents('isekai_source.html', $html);
echo "HTML downloaded. Size: " . strlen($html) . "\n";

// Coba extract semua chapter angka
preg_match_all('/<li[^>]*data-num="(\d+(?:\.\d+)?)"[^>]*>/is', $html, $matches);
$chapters = $matches[1];
$chapters = array_unique($chapters);
sort($chapters, SORT_NUMERIC);

echo "Found " . count($chapters) . " chapters.\n";
echo "Chapters: " . implode(', ', $chapters) . "\n";

// Cek gap
echo "\nChecking Logic Gap (1-30):\n";
for ($i = 1; $i <= 30; $i++) {
    if (!in_array((string)$i, $chapters)) {
        echo "MISSING: Chapter $i\n";
    }
}
