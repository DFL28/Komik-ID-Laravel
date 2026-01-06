<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://westmanga.me/comic/taida-na-akujo-kizoku-ni-tensei-shita-ore-shinario-o-bukko-wa-shitara-kikaku-gai-no-maryoku-de-saikyou-ni-natta';
echo "Checking Detail: $url\n";

$response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$html = $response->body();

echo "Status: " . $response->status() . "\n";
echo "Size: " . strlen($html) . "\n";

// Try Remix Data
$slug = 'taida-na-akujo-kizoku-ni-tensei-shita-ore-shinario-o-bukko-wa-shitara-kikaku-gai-no-maryoku-de-saikyou-ni-natta';
$dataUrl = "https://westmanga.me/comic/$slug?_data=routes%2Fcomic.%24slug"; 

echo "Checking Remix Data: $dataUrl\n";
$res = Http::get($dataUrl);
echo "Status: " . $res->status() . "\n";
if ($res->successful()) {
    echo "Content Type: " . $res->header('Content-Type') . "\n";
    $json = $res->json();
    if ($json) {
        echo "Found JSON Data!\n";
        print_r(array_keys($json));
    } else {
        echo "Response is not JSON.\n";
    }
} else {
     // Try generic root data
     $rootUrl = "https://westmanga.me/?_data=root";
     echo "Checking Root Data: $rootUrl\n";
     $res = Http::get($rootUrl);
     echo "Status: " . $res->status() . "\n";
}

// Check if React hydration data contains chapters
if (preg_match('/window\.__remixContext\s*=\s*({.*?});/s', $html, $m) || preg_match('/w:\[(.*?)\]/', $html, $m)) {
    echo "Found generic JS data block.\n";
}
