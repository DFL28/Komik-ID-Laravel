<?php
$nodePath = '"C:\\Program Files\\nodejs\\node.exe"';
$script = __DIR__ . '\scraper.js';
$url = 'https://kiryuu03.com/manga/solo-leveling/';
$mode = 'detail';

$cmd = "$nodePath \"$script\" \"$url\" \"$mode\" 2>&1";
$output = shell_exec($cmd);

// Parse JSON from output
preg_match('/\{.*\}/s', $output, $matches);
if (!empty($matches[0])) {
    $data = json_decode($matches[0], true);
    
    echo "📖 Title: " . $data['title'] . "\n";
    echo "🖼️  Cover: " . $data['cover'] . "\n";
    echo "📚 Chapters: " . count($data['chapters']) . "\n";
    echo "🏷️  Genres: " . count($data['genres']) . "\n\n";
    
    // Check if cover is valid
    $coverLower = strtolower($data['cover']);
    $adKeywords = ['royal', 'casino', 'betting', 'slot', 'judi', 'banner', 'ads', 'iklan'];
    $hasAd = false;
    
    foreach ($adKeywords as $keyword) {
        if (str_contains($coverLower, $keyword)) {
            $hasAd = true;
            echo "⚠️  WARNING: Cover contains ad keyword: $keyword\n";
            break;
        }
    }
    
    if (str_ends_with($coverLower, '.gif')) {
        echo "⚠️  WARNING: Cover is animated GIF (possibly ad)\n";
    }
    
    if (!$hasAd && !str_ends_with($coverLower, '.gif')) {
        echo "✅ Cover looks valid!\n";
    }
} else {
    echo "❌ Failed to parse JSON output\n";
    echo "Output:\n$output\n";
}
