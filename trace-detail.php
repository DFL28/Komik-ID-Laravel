<?php

$html = file_get_contents('detail_source.html');

echo "--- SEARCHING FOR 'Sinopsis' ---\n";
$pos = stripos($html, 'sinopsis');
if ($pos !== false) {
    echo "Found at $pos. Context:\n";
    echo substr($html, $pos, 500) . "\n";
} else {
    echo "Word 'sinopsis' NOT found.\n";
}

echo "\n--- SEARCHING FOR 'Chapter' ---\n";
// Cari salah satu chapter number, misal 10 atau 1
if (preg_match('/Chapter\s+\d+/', $html, $match, PREG_OFFSET_CAPTURE)) {
    $pos = $match[0][1];
    echo "Found '{$match[0][0]}' at $pos. Context:\n";
    // Mundur dikit untuk lihat parent tag
    echo substr($html, $pos - 200, 400) . "\n";
} else {
    echo "Word 'Chapter X' NOT found.\n";
}
