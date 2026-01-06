<?php

$html = file_get_contents('source.html');

if (preg_match('/ts_reader\.run\((.*?)\);/s', $html, $match)) {
    $jsonStr = $match[1];
    $data = json_decode($jsonStr, true);
    
    if (isset($data['sources'])) {
        echo "Found sources!\n";
        foreach ($data['sources'] as $source) {
            echo "Source: " . $source['source'] . "\n";
            echo "Images count: " . count($source['images']) . "\n";
            if (count($source['images']) > 0) {
                echo "First image: " . $source['images'][0] . "\n";
            }
        }
    } else {
        echo "JSON decoded but no sources found.\n";
        print_r($data);
    }
} else {
    echo "Pattern not found.\n";
}
