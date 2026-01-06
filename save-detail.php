<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'https://komikcast.co.id/manga/boruto-two-blue-vortex/'; 
$response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
file_put_contents('detail_source.html', $response->body());
echo "Saved detail_source.html";
