<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Scraper\KomikcastScraper;

echo "Testing Updated Komikcast Scraper...\n\n";

$scraper = new KomikcastScraper();

try {
    $mangaList = $scraper->fetchMangaList(1);
    
    echo "Found " . count($mangaList) . " manga:\n";
    echo "=================================\n\n";
    
    foreach (array_slice($mangaList, 0, 5) as $i => $manga) {
        echo ($i+1) . ". " . $manga['title'] . "\n";
        echo "   URL: " . $manga['url'] . "\n";
        echo "   Cover: " . substr($manga['cover'], 0, 60) . "...\n\n";
    }
    
    if (count($mangaList) > 0) {
        echo "\nTesting detail fetch for first manga...\n";
        echo "========================================\n\n";
        
        $detail = $scraper->fetchMangaDetail($mangaList[0]);
        
        echo "Title: " . $detail['title'] . "\n";
        echo "Slug: " . $detail['slug'] . "\n";
        echo "Alt Title: " . ($detail['alternative_title'] ?? 'N/A') . "\n";
        echo "Author: " . ($detail['author'] ?? 'N/A') . "\n";
        echo "Status: " . $detail['status'] . "\n";
        echo "Type: " . $detail['type'] . "\n";
        echo "Genres: " . implode(', ', $detail['genres']) . "\n";
        echo "Rating: " . $detail['rating'] . "\n";
        echo "Chapters: " . count($detail['chapters']) . "\n";
        echo "Description: " . substr($detail['description'] ?? '', 0, 100) . "...\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
