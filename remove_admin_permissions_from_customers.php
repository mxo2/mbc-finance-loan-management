<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Removing admin permissions from all customers...\n";

// Admin permissions that customers should NOT have
$adminPermissions = [
    'manage loan type',
    'create loan type',
    'edit loan type', 
    'delete loan type',
    'manage document type',
    'create document type',
    'edit document type',
    'delete document type',
    'manage account type',
    'create account type',
    'edit account type',
    'delete account type',
    'manage notification',
    'create notification',
    'edit notification',
    'delete notification',
    'manage branch',
    'create branch',
    'edit branch',
    'delete branch',
    'manage user',
    'create user',
    'edit user',
    'delete user',
    'manage role',
    'create role',
    'edit role',
    'delete role',
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
    'manage auth page'
];

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
echo "- Customers now have proper access controls\n";

// Now check customer permissions
echo "\nVerifying customer permissions after cleanup:\n";

$testCustomer = $customers->first();
if ($testCustomer) {
    echo "\nChecking permissions for sample customer: {$testCustomer->name}\n";
    
    // Check what permissions they still have
    $validPermissions = [
        'manage loan',
        'create loan', 
        'show loan',
        'show loan type',
        'manage contact',
        'create contact',
        'edit contact',
        'delete contact',
        'manage note',
        'create note',
        'edit note',
        'delete note',
        'manage account',
        'show account',
        'manage transaction',
        'manage repayment',
        'manage account settings',
        'manage password settings',
        'manage 2FA settings'
    ];
    
    echo "Valid customer permissions:\n";
    foreach ($validPermissions as $permission) {
        $hasPermission = $testCustomer->hasPermissionTo($permission);
        echo "  " . ($hasPermission ? "✓" : "✗") . " $permission\n";
    }
    
    // Check they don't have admin permissions
    echo "\nVerifying NO admin permissions:\n";
    $sampleAdminPerms = ['manage loan type', 'manage document type', 'manage user', 'manage settings'];
    foreach ($sampleAdminPerms as $permission) {
        $hasPermission = $testCustomer->hasPermissionTo($permission);
        echo "  " . ($hasPermission ? "✗ STILL HAS" : "✓ REMOVED") . " $permission\n";
    }
}

echo "\n✅ Admin permission cleanup completed!\n";
echo "Customers will no longer see System Configuration section in their menu.\n";