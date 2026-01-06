<?php
$nodePath = '"C:\\Program Files\\nodejs\\node.exe"';
$script = __DIR__ . '\scraper.js';
// Try a popular manga with confirmed chapters
$url = 'https://kiryuu03.com/manga/solo-leveling/';
$mode = 'detail';

$cmd = "$nodePath \"$script\" \"$url\" \"$mode\" 2>&1";

echo "Running: $cmd\n";
echo "Testing with One Piece (popular manga with many chapters)...\n\n";
$output = shell_exec($cmd);

echo "Output:\n";
echo $output;
