<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing Schema::hasTable...\n";
    
    $connection = DB::connection();
    echo "Connection: " . get_class($connection) . "\n";
    
    $schemaBuilder = $connection->getSchemaBuilder();
    echo "Schema Builder: " . get_class($schemaBuilder) . "\n";
    
    echo "\nTesting hasTable('migrations')...\n";
    $has = $schemaBuilder->hasTable('migrations');
    echo "Result: " . ($has ? 'true' : 'false') . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
