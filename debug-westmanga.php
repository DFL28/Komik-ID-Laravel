<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Checking WestManga.me...\n";
$response = Http::get('https://westmanga.me/');
$html = $response->body();
echo "Home size: " . strlen($html) . "\n";
// Dump title
preg_match('/<title>(.*?)<\/title>/', $html, $tm);
echo "Page Title: " . ($tm[1] ?? 'None') . "\n";
echo "First 500 chars:\n" . substr($html, 0, 500) . "\n";

// Dump Scripts to find JSON
preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scripts);
foreach ($scripts[1] as $script) {
    if (strlen($script) > 100) {
        if (strpos($script, '{') !== false) {
             echo "\n--- FOUND SCRIPT WITH JSON CANDIDATE ---\n";
             echo substr($script, 0, 500) . "\n...\n";
        }
    }
}

// Check Detail Page
echo "\n--- CHECKING DETAIL PAGE ---\n";
if (preg_match('/<a href="(https:\/\/westmanga\.me\/manga\/[^"]+)"/', $html, $m)) {
    $detailUrl = $m[1];
    echo "Detail URL: $detailUrl\n";
    
    $detailHtml = Http::get($detailUrl)->body();
    
    // Check Title
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/', $detailHtml, $tm)) {
        echo "Title: " . $tm[1] . "\n";
    }
    
    // Check Chapter List (Westmanga uses generic UL usually)
    if (strpos($detailHtml, 'chapter-list') !== false || strpos($detailHtml, 'clstyle') !== false) {
        echo "Found standard chapter list marker.\n";
    } else {
        echo "No standard chapter list marker found.\n";
    }
    
}
