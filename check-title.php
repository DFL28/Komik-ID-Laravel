<?php
$html = file_get_contents('detail_source.html');
if (preg_match('/<h1[^>]*>(.*?)<\/h1>/', $html, $m)) {
    echo "H1: " . strip_tags($m[1]) . "\n";
} else {
    echo "NO H1 FOUND\n";
}
// Cek container chapter list lagi dengan regex yang sangat longgar
if (preg_match('/id="chapterlist"/', $html)) {
    echo "HAS #chapterlist\n";
} else {
    echo "NO #chapterlist\n";
}
