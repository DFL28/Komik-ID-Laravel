<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://komikcast.co.id/manga/eleceed/'; 
$response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$html = $response->body();

if (preg_match('/id="chapterlist".*?<ul>(.*?)<\/ul>/is', $html, $match)) {
    echo "CHAPTER LIST FOUND:\n";
    echo substr($match[1], 0, 1000) . "\n";
} else {
    echo "CHAPTER LIST NOT FOUND BY REGEX\n";
    // Search for any chapter link
    if (preg_match_all('/Chapter\s+\d+/i', $html, $matches)) {
        echo "Found " . count($matches[0]) . " 'Chapter X' occurrences.\n";
        // Print context of one
        preg_match('/.{50}Chapter\s+\d+.{50}/is', $html, $context);
        echo "Context: " . $context[0] . "\n";
    }
}
