<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all customer roles
$customerRoles = \Spatie\Permission\Models\Role::where('name', 'customer')->get();

echo "Found " . $customerRoles->count() . " customer roles\n";

// Permissions customer should have
$shouldHavePermissions = [
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

// Permissions customer should NOT have
$shouldNotHavePermissions = [
    'create loan type',
    'edit loan type',
    'delete loan type',
    'manage loan type',
    'edit loan',
    'delete loan'
];

// Check each customer role
foreach ($customerRoles as $role) {
    echo "\nVerifying customer role ID: " . $role->id . "\n";
    
    // Check for permissions that should exist
    foreach ($shouldHavePermissions as $permission) {
        $hasPermission = $role->hasPermissionTo($permission);
        echo "  Permission '$permission': " . ($hasPermission ? "✓" : "✗") . "\n";
    }
    
    // Check for permissions that should NOT exist
    foreach ($shouldNotHavePermissions as $permission) {
        $hasPermission = $role->hasPermissionTo($permission);
        echo "  Permission '$permission': " . (!$hasPermission ? "✓" : "✗") . " (should not have)\n";
    }
}

// Now check individual customers
$customers = \App\Models\User::where('type', 'customer')->get();
echo "\nFound " . $customers->count() . " customers to verify\n";

// Process each customer
foreach ($customers as $customer) {
    echo "\nVerifying customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Check for permissions that should exist
    foreach ($shouldHavePermissions as $permission) {
        $hasPermission = $customer->hasPermissionTo($permission);
        echo "  Permission '$permission': " . ($hasPermission ? "✓" : "✗") . "\n";
    }
    
    // Check for permissions that should NOT exist
    foreach ($shouldNotHavePermissions as $permission) {
        $hasPermission = $customer->hasPermissionTo($permission);
        echo "  Permission '$permission': " . (!$hasPermission ? "✓" : "✗") . " (should not have)\n";
    }
}

echo "\nPermission verification complete.\n";