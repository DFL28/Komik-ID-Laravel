<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AICategorizationService;

echo "🤖 Testing AI Categorization Service\n";
echo "=====================================\n\n";

// Check API key
$apiKey = env('GEMINI_API_KEY');
if (empty($apiKey)) {
    echo "⚠️  WARNING: GEMINI_API_KEY not set in .env\n";
    echo "Using fallback keyword-based categorization\n\n";
} else {
    echo "✓ Gemini API Key configured\n\n";
}

$aiService = new AICategorizationService();

// Test cases
$testCases = [
    [
        'title' => 'Solo Leveling',
        'description' => 'A weak hunter becomes the strongest through a mysterious system that allows him to level up infinitely in a world of monsters and dungeons.'
    ],
    [
        'title' => 'My Girlfriend is a Stalker',
        'description' => 'A romantic comedy about a high school boy who discovers his dream girl has been secretly stalking him for years.'
    ],
    [
        'title' => 'Sword Art Online',
        'description' => 'Players are trapped in a virtual reality MMORPG where death in the game means death in real life.'
    ]
];

foreach ($testCases as $i => $test) {
    echo "[" . ($i+1) . "] Testing: {$test['title']}\n";
    echo str_repeat("-", 60) . "\n";
    
    $genres = $aiService->categorizeManga($test['title'], $test['description']);
    
    echo "Genres: " . (empty($genres) ? 'None' : implode(', ', $genres)) . "\n\n";
    
    sleep(1); // Rate limiting
}

echo "✅ Test Complete!\n";
