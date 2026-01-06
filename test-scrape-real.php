<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScraperService;
use App\Services\ImageService;
use Illuminate\Support\Facades\Log;

// Log::shouldReceive removed to avoid missing Mockery dependency
// Logs will go to storage/logs/laravel.log or scraper.log as configured in app

echo "Initializing Scraper Service for Kiryuu...\n";
$service = new ScraperService(new ImageService());

echo "Running Fetch Manga List (1 Page)...\n";
// Gunakan metode fetch list langsung dari scraper instance (bypass circuit breaker service sementara)
// Atau pakai scrape() service tapi mock context.
// Akses property scraper via Reflection karena protected
$reflection = new ReflectionClass($service);
$property = $reflection->getProperty('scraper');
$property->setAccessible(true);
$kiryuuScraper = $property->getValue($service);

$mangaList = $kiryuuScraper->fetchMangaList(1);
echo "Found " . count($mangaList) . " manga items.\n";

if (count($mangaList) > 0) {
    echo "First Item:\n";
    print_r(($mangaList[0]));
    
    // Test Detail Fetch for first item
    echo "\nTesting Detail Scrape for: " . $mangaList[0]['title'] . "\n";
    $detail = $kiryuuScraper->fetchMangaDetail($mangaList[0]);
    echo "Description found: " . (isset($detail['description']) ? 'Yes' : 'No') . "\n";
    echo "Chapters found: " . (isset($detail['chapters']) ? count($detail['chapters']) : 0) . "\n";
    
    if (!empty($detail['chapters'])) {
        echo "First Chapter: " . $detail['chapters'][0]['title'] . " (" . $detail['chapters'][0]['source_url'] . ")\n";
        
        // Test Chapter Image Fetch
        echo "\nTesting Image Fetch for First Chapter...\n";
        $images = $kiryuuScraper->fetchChapterImages($detail['chapters'][0]['source_url']);
        echo "Images found: " . count($images) . "\n";
        if (count($images) > 0) echo "Sample Image: " . $images[0] . "\n";
    }
} else {
    echo "FAILED to fetch manga list.\n";
}
