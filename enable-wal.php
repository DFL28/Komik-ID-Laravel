<?php
// Enable SQLite WAL mode
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$path = database_path('database.sqlite');
echo "Database: $path\n";

if (file_exists($path)) {
    $pdo = new PDO('sqlite:' . $path);
    $pdo->exec('PRAGMA journal_mode = WAL;');
    $pdo->exec('PRAGMA synchronous = NORMAL;');
    echo "WAL mode enabled successfully!\n";
    
    // Validasi
    $res = $pdo->query('PRAGMA journal_mode;');
    echo "Current Journal Mode: " . $res->fetchColumn() . "\n";
} else {
    echo "Database file not found!\n";
}
