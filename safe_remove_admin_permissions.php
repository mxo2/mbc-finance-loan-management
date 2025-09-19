<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Removing admin permissions from all customers (safe version)...\n";

// Get all existing permissions in the system
$allExistingPermissions = \Spatie\Permission\Models\Permission::pluck('name')->toArray();
echo "Found " . count($allExistingPermissions) . " permissions in the system.\n";

// Admin permissions that customers should NOT have (only those that exist)
$adminPermissionsToCheck = [
    'manage loan type',
    'create loan type',
    'edit loan type', 
    'delete loan type',
    'manage document type',
    'manage account type',
    'manage notification',
    'manage branch',
    'manage user',
    'manage role',
    'manage settings',
    'manage general settings',
    'manage email settings',
    'manage payment settings',
    'manage company settings',
    'manage seo settings',
    'manage google recaptcha settings',
    'manage pricing packages',
    'manage pricing transation',
    'manage coupon',
    'manage coupon history',
    'manage FAQ',
    'manage Page',
    'manage home page',
    'manage footer',
    'manage auth page',
    'manage logged history'
];

// Filter to only existing permissions
$adminPermissions = array_intersect($adminPermissionsToCheck, $allExistingPermissions);
echo "Will check " . count($adminPermissions) . " admin permissions.\n";

// Get all customers
$customers = \App\Models\User::where('type', 'customer')->get();
echo "Found " . $customers->count() . " customers to process.\n";

$totalRemovedPermissions = 0;

foreach ($customers as $customer) {
    echo "\nProcessing customer: {$customer->name} (ID: {$customer->id})\n";
    
    $removedFromThisCustomer = 0;
    
    foreach ($adminPermissions as $permission) {
        if ($customer->hasPermissionTo($permission)) {
            try {
                $customer->revokePermissionTo($permission);
                echo "  ✓ Removed permission: $permission\n";
                $removedFromThisCustomer++;
                $totalRemovedPermissions++;
            } catch (Exception $e) {
                echo "  ✗ Failed to remove permission: $permission - " . $e->getMessage() . "\n";
            }
        }
    }
    
    if ($removedFromThisCustomer == 0) {
        echo "  ✓ No admin permissions found - customer is properly configured\n";
    } else {
        echo "  ✓ Removed $removedFromThisCustomer admin permissions from this customer\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY:\n";
echo "- Processed " . $customers->count() . " customers\n";
echo "- Removed $totalRemovedPermissions admin permissions total\n";

// Verify a test customer
$testCustomer = $customers->first();
if ($testCustomer) {
    echo "\nVerifying sample customer: {$testCustomer->name}\n";
    
    // Check they don't have key admin permissions
    $keyAdminPerms = ['manage loan type', 'manage document type', 'manage user', 'manage settings'];
    $hasAdminAccess = false;
    foreach ($keyAdminPerms as $permission) {
        if (in_array($permission, $allExistingPermissions) && $testCustomer->hasPermissionTo($permission)) {
            echo "  ✗ STILL HAS: $permission\n";
            $hasAdminAccess = true;
        }
    }
    
    if (!$hasAdminAccess) {
        echo "  ✓ Customer has no admin permissions\n";
    }
    
    // Check they have proper customer permissions
    $customerPerms = ['show loan', 'show loan type', 'manage contact'];
    echo "\nCustomer permissions:\n";
    foreach ($customerPerms as $permission) {
        if (in_array($permission, $allExistingPermissions)) {
            $hasPermission = $testCustomer->hasPermissionTo($permission);
            echo "  " . ($hasPermission ? "✓" : "✗") . " $permission\n";
        }
    }
}

echo "\n✅ Customer permission cleanup completed!\n";
echo "Customers will no longer see System Configuration section.\n";