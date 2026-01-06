<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Database Config:\n";
print_r(config('database.connections.sqlite'));

echo "\n\nDatabase Default:\n";
echo config('database.default');
