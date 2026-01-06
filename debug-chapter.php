<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

// Gunakan URL chapter yang valid dari log sebelumnya/umum
$url = 'https://komikcast.co.id/eleceed-chapter-320/'; 
echo "Testing Chapter Image Fetch: $url\n";

$response = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Referer' => 'https://komikcast.co.id/'
])->get($url);

$html = $response->body();

echo "HTML Length: " . strlen($html) . "\n";

// Cek regex yang sekarang dipakai
$currentRegex = '/<img[^>]*class="[^"]*ts-main-image[^"]*"[^>]*(?:src|data-src)="([^"]+)"/i';
preg_match_all($currentRegex, $html, $matches);
echo "Current Regex Matches: " . count($matches[1]) . "\n";

// Jika 0, mari kita lihat snippet HTML yang mengandung gambar
// Biasanya gambar ada di container #readerarea atau .main-reading-area
if (preg_match('/id="readerarea"[^>]*>(.*?)<\/div>/is', $html, $area)) {
    echo "Found #readerarea. Dumping snippet...\n";
    // Tampilkan 500 karakter pertama dari area gambar
    echo substr($area[1], 0, 500) . "\n...\n";
    
    // Coba cari semua tag img di dalamnya dengan regex generik
    preg_match_all('/<img[^>]+src="([^"]+)"/i', $area[1], $imgMatches);
    echo "Generic Img Src Matches in Area: " . count($imgMatches[1]) . "\n";
    if (count($imgMatches[1]) > 0) {
        echo "Example: " . $imgMatches[1][0] . "\n";
    }
} else {
    echo "READING AREA NOT FOUND!\n";
    // Dump sembarang tag img untuk petunjuk
    preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $allImgs);
    echo "Total images on page: " . count($allImgs[1]) . "\n";
}
