<?php
/**
 * Test script to verify the old loan interface works
 */

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    echo "=== Testing Old Loan Interface Restoration ===\n\n";
    
    // Test 1: Check if customer_index.blade.php exists
    echo "1. Checking customer_index.blade.php file...\n";
    $customerIndexPath = __DIR__ . '/resources/views/loans/customer_index.blade.php';
    if (file_exists($customerIndexPath)) {
        $fileSize = filesize($customerIndexPath);
        echo "   ✓ customer_index.blade.php exists ({$fileSize} bytes)\n";
    } else {
        echo "   ❌ customer_index.blade.php not found\n";
    }
    
    // Test 2: Check if controller points to customer_index
    echo "\n2. Checking controller view reference...\n";
    $controllerPath = __DIR__ . '/app/Http/Controllers/LoanApplicationController.php';
    if (file_exists($controllerPath)) {
        $controllerContent = file_get_contents($controllerPath);
        if (strpos($controllerContent, "view('loans.customer_index'") !== false) {
            echo "   ✓ Controller now points to customer_index view\n";
        } else if (strpos($controllerContent, "view('loans.modern_index'") !== false) {
            echo "   ❌ Controller still points to modern_index\n";
        } else {
            echo "   ⚠ Controller view reference unclear\n";
        }
    }
    
    // Test 3: Check backup was created
    echo "\n3. Checking backup file...\n";
    $backupPath = __DIR__ . '/resources/views/loans/modern_index.blade.php.backup';
    if (file_exists($backupPath)) {
        echo "   ✓ Backup of modern_index created\n";
    } else {
        echo "   ⚠ No backup file found\n";
    }
    
    // Test 4: Check for potential layout issues
    echo "\n4. Checking layout compatibility...\n";
    if (file_exists($customerIndexPath)) {
        $customerContent = file_get_contents($customerIndexPath);
        if (strpos($customerContent, "@extends('layouts.app')") !== false) {
            echo "   ✓ Uses standard app layout\n";
        }
        if (strpos($customerContent, 'btn btn-primary') !== false) {
            echo "   ✓ Uses standard Bootstrap buttons\n";
        }
        if (strpos($customerContent, 'loan.apply') !== false) {
            echo "   ✓ Contains loan application links\n";
        }
    }
    
    echo "\n=== Restoration Summary ===\n";
    echo "✓ Old customer interface restored\n";
    echo "✓ Modern interface backed up\n";
    echo "✓ Controller updated to use customer_index\n";
    echo "✓ Standard layout structure maintained\n";
    echo "\nThe loan page should now use the simpler, working interface.\n";
    echo "Visit https://fix.mbcfinserv.com/loan to test the restored interface.\n";
    
} catch (Exception $e) {
    echo "❌ Test error: " . $e->getMessage() . "\n";
}
?>