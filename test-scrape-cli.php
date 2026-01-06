<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScraperService;

$service = app(ScraperService::class);
echo "Testing Scraper (1 page, no download)...\n";

try {
    $result = $service->scrape(1, false);
    echo "SUCCESS!\n";
    echo "Count: " . $result['count'] . "\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
