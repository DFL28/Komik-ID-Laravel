<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 Testing VERIFIED Manga Sources\n";
echo "===================================\n\n";

// These are verified working sites (as of 2024)
$sources = [
    'KomikCast' => 'https://komikcast.cz',
    'Komiku' => 'https://komiku.com',  // Changed from .id
    'MangaKita' => 'https://mangakita.net',
];

foreach ($sources as $name => $url) {
    echo "Testing {$name} ({$url})...\n";
    
    try {
        $response = Http::timeout(15)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        ])->get($url);
        
        if ($response->successful()) {
            $html = $response->body();
            $status = $response->status();
            $size = strlen($html);
            
            // Protections
            $hasCloudflare = (stripos($html, 'cloudflare') !== false || stripos($html, 'cf-browser') !== false) && $size < 5000;
            $hasJSChallenge = stripos($html, 'jschl') !== false;
            
            // Content check
            $hasContent = stripos($html, '<html') !== false && $size > 10000;
            $hasMangaContent = (stripos($html, 'komik') !== false || stripos($html, 'manga') !== false || stripos($html, 'chapter') !== false);
            
            echo "  ✓ Connected Successfully!\n";
            echo "  • Status: {$status}\n";
            echo "  • Size: " . number_format($size) . " bytes\n";
            echo "  • Cloudflare Block: " . ($hasCloudflare ? "YES ⚠️" : "NO ✓") . "\n";
            echo "  • Valid HTML: " . ($hasContent ? "YES ✓" : "NO ⚠️") . "\n";
            echo "  • Manga Content: " . ($hasMangaContent ? "YES ✓" : "NO ⚠️") . "\n";
            
            // Try to find manga links
            preg_match_all('/<a[^>]*href=["\']([^"\']*(?:manga|komik|series)[^"\']*)["\'][^>]*>/', $html, $matches);
            $mangaLinks = array_filter($matches[1], function($link) {
                return !empty($link) && !str_contains($link, '#') && !str_contains($link, 'javascript:');
            });
            $uniqueLinks = array_unique(array_slice($mangaLinks, 0, 5));
            
            if (!empty($uniqueLinks)) {
                echo "  • Sample Links Found:\n";
                foreach ($uniqueLinks as $link) {
                    echo "    - " . (str_starts_with($link, 'http') ? $link : $url . '/' . ltrim($link, '/')) . "\n";
                }
            }
            
            echo "  ⭐ RECOMMENDATION: " . ($hasContent && $hasMangaContent && !$hasCloudflare ? "GOOD TO USE ✓" : "SKIP ✗") . "\n";
            echo "\n";
        } else {
            echo "  ✗ HTTP Error: {$response->status()}\n\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ Connection Error: " . substr($e->getMessage(), 0, 100) . "...\n\n";
    }
    
    sleep(3);
}

echo "✅ Testing Complete!\n";
