<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear permission cache if it exists
if (method_exists(\Spatie\Permission\PermissionRegistrar::class, 'forgetCachedPermissions')) {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    echo "Permission cache cleared.\n\n";
}

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

// Check each customer role using direct DB queries
foreach ($customerRoles as $role) {
    echo "\nVerifying customer role ID: " . $role->id . "\n";
    
    $directRolePermissions = $role->permissions()->pluck('name')->toArray();
    
    // Check for permissions that should exist
    foreach ($shouldHavePermissions as $permission) {
        $hasPermission = in_array($permission, $directRolePermissions);
        echo "  Permission '$permission': " . ($hasPermission ? "✓" : "✗") . "\n";
    }
    
    // Check for permissions that should NOT exist
    foreach ($shouldNotHavePermissions as $permission) {
        $hasPermission = in_array($permission, $directRolePermissions);
        echo "  Permission '$permission': " . (!$hasPermission ? "✓" : "✗") . " (should not have)\n";
    }
}

// Now check individual customers with direct DB queries
$customers = \App\Models\User::where('type', 'customer')->get();
echo "\nFound " . $customers->count() . " customers to verify\n";

// Process each customer
foreach ($customers as $customer) {
    echo "\nVerifying customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Get direct permissions from DB
    $directUserPermissions = $customer->permissions()->pluck('name')->toArray();
    
    // Get permissions via roles
    $rolePermissions = collect([]);
    foreach ($customer->roles as $role) {
        $rolePermissions = $rolePermissions->merge($role->permissions()->pluck('name'));
    }
    $effectivePermissions = array_unique(array_merge($directUserPermissions, $rolePermissions->toArray()));
    
    // Check for permissions that should exist
    foreach ($shouldHavePermissions as $permission) {
        $hasPermission = in_array($permission, $effectivePermissions);
        echo "  Permission '$permission': " . ($hasPermission ? "✓" : "✗") . "\n";
    }
    
    // Check for permissions that should NOT exist
    foreach ($shouldNotHavePermissions as $permission) {
        $hasPermission = in_array($permission, $effectivePermissions);
        echo "  Permission '$permission': " . (!$hasPermission ? "✓" : "✗") . " (should not have)\n";
    }
}

echo "\nPermission verification complete.\n";