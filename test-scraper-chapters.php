<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Scraper\KiryuuScraper;

echo "🧪 Testing Kiryuu Scraper Chapter URL Extraction\n";
echo "=================================================\n\n";

$scraper = new KiryuuScraper();

// Test with known manga
$testUrl = 'https://kiryuu03.com/manga/kusozako-choroin-nishiga-hachi-pre-serialization/';

echo "📖 Testing: $testUrl\n\n";

$entry = [
    'title' => 'Test Manga',
    'url' => $testUrl,
    'slug' => 'test',
    'cover_path' => '',
    'genres' => [],
    'chapters' => [],
    'description' => ''
];

$result = $scraper->fetchMangaDetail($entry);

echo "📚 Chapters Found: " . count($result['chapters']) . "\n\n";

if (!empty($result['chapters'])) {
    echo "First 3 Chapters:\n";
    echo "-----------------\n";
    foreach (array_slice($result['chapters'], 0, 3) as $i => $ch) {
        echo "\n[" . ($i+1) . "] Chapter {$ch['number']}: {$ch['title']}\n";
        echo "    URL Key 'url': " . ($ch['url'] ?? '❌ MISSING') . "\n";
        echo "    URL Key 'source_url': " . ($ch['source_url'] ?? '❌ MISSING') . "\n";
    }
} else {
    echo "❌ No chapters found!\n";
}
