<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://komikcast.co.id/eleceed-chapter-366/'; 
$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
])->get($url);
$html = $response->body();

echo "Searching for images...\n";

// Mencari semua tag img
preg_match_all('/<img[^>]+>/i', $html, $matches);
echo "Found " . count($matches[0]) . " image tags.\n";

$count = 0;
foreach ($matches[0] as $imgTag) {
    // Filter gambar kecil/icon
    if (strpos($imgTag, 'wp-content/uploads') !== false) {
        $count++;
        if ($count < 10) {
            echo $imgTag . "\n";
        }
    }
}
