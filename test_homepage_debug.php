<?php
/**
 * Quick test of homepage functionality
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    echo "=== Testing Homepage Route ===\n\n";
    
    // Test the route logic manually
    echo "1. Testing file paths...\n";
    
    $frontendPath = base_path('public/frontend/index.html');
    $devPath = base_path('FinanceFlow/FinanceFlow/dist/public/index.html');
    
    echo "   Production path: " . $frontendPath . "\n";
    echo "   Production exists: " . (file_exists($frontendPath) ? "✅ YES" : "❌ NO") . "\n";
    
    echo "   Development path: " . $devPath . "\n";
    echo "   Development exists: " . (file_exists($devPath) ? "✅ YES" : "❌ NO") . "\n";
    
    // Test what the route should return
    if (file_exists($frontendPath)) {
        echo "   → Would serve: Production frontend\n";
        $content = file_get_contents($frontendPath);
        echo "   → Content length: " . strlen($content) . " bytes\n";
    } elseif (file_exists($devPath)) {
        echo "   → Would serve: Development frontend\n";
        $content = file_get_contents($devPath);
        echo "   → Content length: " . strlen($content) . " bytes\n";
        if (strpos($content, '<div id="root">') !== false) {
            echo "   → Content type: React application ✅\n";
        }
    } else {
        echo "   → Would serve: Welcome fallback page\n";
    }
    
    // Test if Laravel can create responses
    echo "\n2. Testing Laravel response...\n";
    try {
        $response = response('Test response');
        echo "   ✅ Laravel response system working\n";
    } catch (Exception $e) {
        echo "   ❌ Laravel response error: " . $e->getMessage() . "\n";
    }
    
    // Check for errors in logs
    echo "\n3. Checking for errors...\n";
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logs = file_get_contents($logPath);
        $recentLogs = array_slice(explode("\n", $logs), -20);
        $hasErrors = false;
        
        foreach ($recentLogs as $line) {
            if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                echo "   ⚠ Recent error: " . trim($line) . "\n";
                $hasErrors = true;
            }
        }
        
        if (!$hasErrors) {
            echo "   ✅ No recent errors in logs\n";
        }
    } else {
        echo "   ⚠ No log file found\n";
    }
    
    echo "\n=== Diagnosis Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Bootstrap error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>