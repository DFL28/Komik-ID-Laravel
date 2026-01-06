<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://kiryuu03.com/manga/fantasy-induction-center/';
echo "Fetching: $url\n";
$html = Http::withHeaders(['User-Agent'=>'Mozilla/5.0'])->get($url)->body();

echo "Length: " . strlen($html) . "\n";

// Dump all links
preg_match_all('/<a[^>]*href="([^"]+)"[^>]*>/is', $html, $matches);
$links = array_unique($matches[1]);
echo "Found " . count($links) . " total links.\n";

$chapterCandidates = [];
foreach ($links as $l) {
    // Filter internal links that are likely chapters (usually longer than base)
    if (strpos($l, 'kiryuu03.com') !== false && strlen($l) > 40 && strpos($l, '/manga/') === false) {
        $chapterCandidates[] = $l;
    }
}
echo "Found " . count($chapterCandidates) . " candidates.\n";
print_r(array_slice($chapterCandidates, 0, 10));

if (!empty($chapterCandidates)) {
    // Find context of first candidate
    $first = $chapterCandidates[0];
    $pos = strpos($html, $first);
    echo "\nContext around first candidate:\n";
    echo htmlspecialchars(substr($html, max(0, $pos - 300), 1000));
}

// Extract all classes
preg_match_all('/class="([^"]+)"/i', $html, $classes);
$allClasses = [];
foreach ($classes[1] as $c) {
    foreach (explode(' ', $c) as $part) {
        if (!empty($part)) $allClasses[] = $part;
    }
}
$uniqueClasses = array_unique($allClasses);
sort($uniqueClasses);
echo "Classes Found:\n";
print_r(array_slice($uniqueClasses, 0, 100)); // First 100 classes

// Check HTMX Attributes
echo "\n--- HTMX ATTRIBUTES ---\n";
preg_match_all('/hx-get="([^"]+)"/i', $html, $gets);
if (!empty($gets[1])) {
    echo "Found hx-get endpoints:\n";
    print_r(array_unique($gets[1]));
}

preg_match_all('/hx-post="([^"]+)"/i', $html, $posts);
if (!empty($posts[1])) {
    echo "Found hx-post endpoints:\n";
    print_r(array_unique($posts[1]));
}

// Fetch AJAX Chapter List with proper headers
$ajaxUrl = 'https://kiryuu03.com/wp-admin/admin-ajax.php?manga_id=697692&page=1&action=chapter_list';
echo "\nFetching AJAX: $ajaxUrl\n";
$ajaxHtml = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'X-Requested-With' => 'XMLHttpRequest', // Important for WP AJAX
    'Referer' => 'https://kiryuu03.com/manga/fantasy-induction-center/',
    'HX-Request' => 'true', // HTMX header
])->get($ajaxUrl)->body();

echo "AJAX Length: " . strlen($ajaxHtml) . "\n";
echo "First 500 chars:\n" . substr($ajaxHtml, 0, 500) . "\n";

// Check if links are there
if (preg_match_all('/<a[^>]*href="([^"]+)"[^>]*>/is', $ajaxHtml, $matches)) {
    echo "Found " . count($matches[0]) . " chapter links in AJAX.\n";
    print_r(array_slice($matches[1], 0, 5));
}

// Check Chapter List ID
if (strpos($html, 'chapterlist') !== false) {
    echo "Found 'chapterlist' string.\n";
} else {
    echo "NO 'chapterlist' string found.\n";
}

// Dump generic list items containing chapter links
preg_match_all('/<li[^>]*>.*?<a href="[^"]+chapter[^"]+"[^>]*>.*?<\/a>.*?<\/li>/is', $html, $matches);
echo "Found " . count($matches[0]) . " generic list items with chapter links.\n";

if (count($matches[0]) > 0) {
    echo "Dump first item:\n";
    echo substr($matches[0][0], 0, 500) . "\n";
} else {
    // Try without LI constraint, just A links
    preg_match_all('/<a[^>]*href="([^"]+chapter[^"]+)"[^>]*>/is', $html, $matches);
    echo "Found " . count($matches[0]) . " direct chapter links.\n";
    if (count($matches[0]) > 0) {
        // Dump context around first link
        $pos = strpos($html, $matches[0][0]);
        echo "Context:\n" . substr($html, max(0, $pos - 200), 500) . "\n";
    }
}
