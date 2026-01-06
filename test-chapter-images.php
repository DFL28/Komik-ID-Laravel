<?php
$nodePath = '"C:\\Program Files\\nodejs\\node.exe"';
$script = __DIR__ . '\scraper.js';
// Use actual chapter URL from Bill the Blacksmith Chapter 1
$url = 'https://kiryuu03.com/manga/bill-the-blacksmith/chapter-1.697166/';
$mode = 'chapter';

$cmd = "$nodePath \"$script\" \"$url\" \"$mode\" 2>&1";
echo "Testing Chapter Image Scraping...\n";
echo "URL: $url\n\n";

$output = shell_exec($cmd);

// Parse JSON
preg_match('/\{.*\}/s', $output, $matches);
if (!empty($matches[0])) {
    $data = json_decode($matches[0], true);
    
    echo "📚 Images Found: " . count($data['images']) . "\n\n";
    
    if (!empty($data['images'])) {
        echo "Sample Images:\n";
        foreach (array_slice($data['images'], 0, 5) as $i => $img) {
            echo "  [" . ($i+1) . "] " . $img . "\n";
        }
    } else {
        echo "⚠️  No images found!\n";
        echo "\nFull Output:\n";
        echo $output;
    }
} else {
    echo "❌ Failed to parse JSON\n";
    echo "Output:\n$output\n";
}
