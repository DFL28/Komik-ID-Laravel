<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 Testing Alternative Manga Sources\n";
echo "=====================================\n\n";

$sources = [
    'Komikindo' => 'https://komikindo.pw',
    'Bacakomik' => 'https://bacakomik.co',
    'Komiku' => 'https://komiku.id',
    'Mangaku' => 'https://mangaku.cc',
];

foreach ($sources as $name => $url) {
    echo "Testing {$name} ({$url})...\n";
    
    try {
        $response = Http::timeout(10)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ])->get($url);
        
        if ($response->successful()) {
            $html = $response->body();
            $status = $response->status();
            $size = strlen($html);
            
            // Check for protection
            $hasCloudflare = stripos($html, 'cloudflare') !== false || stripos($html, 'cf-browser') !== false;
            $hasJSChallenge = stripos($html, 'jschl') !== false || stripos($html, 'challenge') !== false;
            
            // Check if it's a real page
            $hasContent = stripos($html, '<html') !== false && $size > 10000;
            
            echo "  ✓ Status: {$status}\n";
            echo "  ✓ Size: " . number_format($size) . " bytes\n";
            echo "  • Cloudflare: " . ($hasCloudflare ? "YES ⚠️" : "NO ✓") . "\n";
            echo "  • JS Challenge: " . ($hasJSChallenge ? "YES ⚠️" : "NO ✓") . "\n";
            echo "  • Has Content: " . ($hasContent ? "YES ✓" : "NO ⚠️") . "\n";
            
            // Quick structure check
            if (stripos($html, 'manga') || stripos($html, 'chapter')) {
                echo "  • Manga/Chapter detected: YES ✓\n";
            }
            
            echo "\n";
        } else {
            echo "  ✗ Failed: HTTP {$response->status()}\n\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }
    
    sleep(2); // Be polite
}

echo "✅ Test Complete!\n";
