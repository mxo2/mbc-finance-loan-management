<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all customer roles
$customerRoles = \Spatie\Permission\Models\Role::where('name', 'customer')->get();

echo "Found " . $customerRoles->count() . " customer roles\n";

// Permissions to keep for customers
$keepPermissions = [
    'manage loan',
    'create loan', 
    'show loan',
    'show loan type', // Keep this to view loan types
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

// Permissions to remove from customers
$removePermissions = [
    'create loan type', // Remove these
    'edit loan type',   // Remove these
    'delete loan type', // Remove these
    'manage loan type', // Change this to a view-only permission
    'edit loan',       // Remove these
    'delete loan'      // Remove these
];

// Process each customer role
foreach ($customerRoles as $role) {
    echo "\nProcessing customer role ID: " . $role->id . "\n";
    
    // Remove unwanted permissions
    foreach ($removePermissions as $permission) {
        $permObj = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj && $role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permObj);
            echo "  - Removed permission: " . $permission . "\n";
        }
    }
    
    // Make sure they have the keep permissions
    foreach ($keepPermissions as $permission) {
        $permObj = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj && !$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permObj);
            echo "  - Added permission: " . $permission . "\n";
        }
    }
}

// Now update individual customers to also remove these permissions
$customers = \App\Models\User::where('type', 'customer')->get();
echo "\nFound " . $customers->count() . " customers to update\n";

// Process each customer
foreach ($customers as $customer) {
    echo "\nProcessing customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Remove unwanted permissions
    $removedCount = 0;
    foreach ($removePermissions as $permission) {
        if ($customer->hasPermissionTo($permission)) {
            $customer->revokePermissionTo($permission);
            $removedCount++;
        }
    }
    
    // Add needed permissions
    $addedCount = 0;
    foreach ($keepPermissions as $permission) {
        $permObj = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj && !$customer->hasPermissionTo($permission)) {
            $customer->givePermissionTo($permObj);
            $addedCount++;
        }
    }
    
    echo "  - Removed " . $removedCount . " permissions\n";
    echo "  - Added " . $addedCount . " missing permissions\n";
}

echo "\nAll customer roles and customers have been updated with correct permissions.\n";
