<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🗑️  Clearing All Manga Data...\n";
echo "================================\n\n";

DB::table('chapters')->truncate();
echo "✓ Deleted all chapters\n";

DB::table('bookmarks')->truncate();
echo "✓ Deleted all bookmarks\n";

DB::table('manga')->truncate();
echo "✓ Deleted all manga\n";

echo "\n✅ Database cleared successfully!\n";
echo "All manga, chapters, and related data have been removed.\n";
