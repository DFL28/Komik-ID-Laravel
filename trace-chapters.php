<?php

$html = file_get_contents('detail_source.html');

echo "--- SEARCHING FOR CHAPTER LINKS ---\n";
// Pattern: href="...-chapter-..."
preg_match_all('/<a[^>]*href="([^"]+chapter-[^"]+)"[^>]*>/i', $html, $matches);

echo "Found " . count($matches[0]) . " chapter links.\n";
if (count($matches[0]) > 0) {
    echo "First 5 matches:\n";
    for ($i = 0; $i < min(5, count($matches[0])); $i++) {
        echo $matches[0][$i] . "\n";
    }
    
    // Check parent container of the first link
    $firstLink = $matches[0][0];
    $pos = strpos($html, $firstLink);
    echo "\nParent Context of first link:\n";
    echo substr($html, $pos - 300, 600) . "\n";
}
