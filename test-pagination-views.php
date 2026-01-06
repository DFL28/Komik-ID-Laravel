<?php
// Test if custom pagination view exists
$viewName = 'pagination.tailwind';
$viewPath = resource_path("views/vendor/pagination/tailwind.blade.php");

echo "Testing Custom Pagination View\n";
echo "================================\n\n";
echo "View Name: {$viewName}\n";
echo "File Path: {$viewPath}\n";
echo "File Exists: " . (file_exists($viewPath) ? 'YES ✓' : 'NO ✗') . "\n";
echo "File Size: " . (file_exists($viewPath) ? filesize($viewPath) . ' bytes' : 'N/A') . "\n";

$files = glob(resource_path("views/vendor/pagination/*.blade.php"));
echo "\nAll Pagination Views Found:\n";
foreach ($files as $file) {
    echo "  - " . basename($file) . "\n";
}
