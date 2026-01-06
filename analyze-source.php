<?php

$html = file_get_contents('source.html');

echo "HTML Length: " . strlen($html) . "\n";

// Cek keberadaan 'ts_reader' variable JSON
if (preg_match('/ts_reader\.run\((.*?)\);/s', $html, $match)) {
    echo "FOUND ts_reader JSON data!\n";
    $json = $match[1];
    echo "JSON Snippet: " . substr($json, 0, 100) . "\n";
} else {
    echo "NO ts_reader JSON found.\n";
}

// Cek keberadaan main-reading-area
if (strpos($html, 'main-reading-area') !== false) {
    echo "FOUND main-reading-area class.\n";
} else {
    echo "NO main-reading-area class found.\n";
}

// Cek img tags lagi
preg_match_all('/<img[^>]+>/i', $html, $matches);
echo "Total img tags: " . count($matches[0]) . "\n";
