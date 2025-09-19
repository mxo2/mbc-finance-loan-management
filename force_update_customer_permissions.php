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
    'create loan type', 
    'edit loan type',   
    'delete loan type', 
    'manage loan type', 
    'edit loan',       
    'delete loan'      
];

// First, remove all permissions from the role and then add back only what we want
foreach ($customerRoles as $role) {
    echo "\nProcessing customer role ID: " . $role->id . "\n";
    
    // Get current permissions 
    $currentPerms = $role->permissions()->pluck('name')->toArray();
    echo "  Current permissions: " . implode(', ', $currentPerms) . "\n";
    
    // Remove all permissions
    $role->permissions()->detach();
    
    // Add back only the allowed permissions
    foreach ($keepPermissions as $permission) {
        $permObj = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj) {
            $role->givePermissionTo($permObj);
            echo "  - Added permission: " . $permission . "\n";
        }
    }
}

// Now update individual customers
$customers = \App\Models\User::where('type', 'customer')->get();
echo "\nFound " . $customers->count() . " customers to update\n";

// Process each customer - also completely reset their permissions
foreach ($customers as $customer) {
    echo "\nProcessing customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Get current permissions
    $currentPerms = $customer->permissions()->pluck('name')->toArray();
    echo "  Current permissions: " . implode(', ', $currentPerms) . "\n";
    
    // Remove all direct permissions
    $customer->permissions()->detach();
    
    // Add back only the allowed permissions
    foreach ($keepPermissions as $permission) {
        $permObj = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj) {
            $customer->givePermissionTo($permObj);
            echo "  - Added permission: " . $permission . "\n";
        }
    }
}

echo "\nAll customer roles and customers have been updated with correct permissions.\n";