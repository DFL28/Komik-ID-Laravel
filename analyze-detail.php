<?php

$html = file_get_contents('detail_source.html');

echo "HTML Length: " . strlen($html) . "\n";

// 1. Cari Sinopsis (biasanya keyword 'sinopsis' atau class 'entry-content' atau 'itemprop="description"')
if (preg_match('/<div[^>]*class="[^"]*sinopsis[^"]*"[^>]*>(.*?)<\/div>/is', $html, $m)) {
    echo "Pattern 'sinopsis' class FOUND.\n";
} else {
    echo "Pattern 'sinopsis' class NOT found.\n";
}

if (strpos($html, 'itemprop="description"') !== false) {
    echo "Found 'itemprop=description'. Context:\n";
    preg_match('/<div[^>]*itemprop="description"[^>]*>(.*?)<\/div>/is', $html, $m);
    echo substr(strip_tags($m[1] ?? 'failed extract'), 0, 100) . "\n";
}

// 2. Cari Chapter List (biasanya id='chapterlist' atau ul class='clstyle')
if (preg_match('/id="chapterlist"/', $html)) {
    echo "Found ID 'chapterlist'.\n";
    // Coba lihat item di dalamnya
    preg_match('/id="chapterlist".*?<ul>(.*?)<\/ul>/is', $html, $list);
    $items = $list[1] ?? '';
    echo "List Items Sample:\n" . substr($items, 0, 300) . "\n";
} elseif (preg_match('/class="clstyle"/', $html)) {
    echo "Found class 'clstyle'.\n";
} else {
    echo "Chapter list container NOT found.\n";
}
