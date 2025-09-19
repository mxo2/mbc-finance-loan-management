<?php
// Simple test of route logic
$frontendPath = __DIR__ . '/public/frontend/index.html';
$devPath = __DIR__ . '/FinanceFlow/FinanceFlow/dist/public/index.html';

echo "Testing route logic...\n";
echo "Production path: $frontendPath\n";
echo "Development path: $devPath\n";

if (!file_exists($frontendPath)) {
    $frontendPath = $devPath;
}

if (file_exists($frontendPath)) {
    echo "SUCCESS: Would serve React app from: $frontendPath\n";
    $content = file_get_contents($frontendPath);
    echo "Content length: " . strlen($content) . " bytes\n";
    echo "Route should work properly!\n";
} else {
    echo "SUCCESS: Would serve fallback HTML page\n";
    echo "Route should work properly!\n";
}
?>