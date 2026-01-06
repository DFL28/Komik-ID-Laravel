<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking Chapter Data in Database\n";
echo "=====================================\n\n";

// Get first manga
$manga = DB::table('manga')->first();

if (!$manga) {
    echo "❌ No manga found in database!\n";
    echo "Please scrape manga first from /admin/scraper\n";
    exit;
}

echo "📖 Manga: {$manga->title}\n";
echo "🆔 ID: {$manga->id}\n\n";

// Get chapters for this manga
$chapters = DB::table('chapters')
    ->where('manga_id', $manga->id)
    ->orderBy('chapter_number', 'asc')
    ->limit(5)
    ->get();

if ($chapters->isEmpty()) {
    echo "❌ No chapters found for this manga!\n";
    exit;
}

echo "📚 First 5 Chapters:\n";
echo "--------------------\n";

foreach ($chapters as $chapter) {
    echo "\nChapter {$chapter->chapter_number}: {$chapter->title}\n";
    echo "  Source URL: " . ($chapter->source_url ?: '❌ MISSING!') . "\n";
    
    // Test if we can scrape this chapter
    if ($chapter->source_url) {
        echo "  ✓ Has URL - Can scrape images\n";
    } else {
        echo "  ✗ NO URL - Cannot scrape!\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Total chapters: " . $chapters->count() . "\n";
