<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Checking Komiku.org...\n";
$html = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get('https://komiku.org/')->body();

echo "Home size: " . strlen($html) . "\n";
echo "First 500 chars:\n" . substr($html, 0, 500) . "\n";

// Check Links
echo "\n--- DUMPING LINKS ---\n";
preg_match_all('/<a[^>]*href="([^"]+)"[^>]*>/i', $html, $matches);
$links = array_unique($matches[1]);
print_r(array_slice($links, 0, 10));

// Check Detail Page Structure if link found
if (!empty($matches[1][0])) {
    $detailUrl = $matches[1][0];
    echo "\nChecking Detail: $detailUrl\n";
    $detailHtml = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($detailUrl)->body();
    
    // Check Chapter List
    if (preg_match('/<ul[^>]*id="chapter-list"[^>]*>/i', $detailHtml) || strpos($detailHtml, 'chapter') !== false) {
        echo "Found chapter list indicator.\n";
    }
}
