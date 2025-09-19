<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the default customer role (the one with ID 3)
$customerRole = Spatie\Permission\Models\Role::find(3);
if (!$customerRole) {
    echo "Default customer role not found\n";
    exit;
}

echo "Updating permissions for role: " . $customerRole->name . " (ID: " . $customerRole->id . ")\n";

// List of required permissions for customers
$requiredPermissions = [
    'manage loan', 
    'create loan', 
    'edit loan', 
    'delete loan', 
    'show loan',
    'manage loan type',
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

// Assign permissions to the role
foreach ($requiredPermissions as $permission) {
    $permObj = Spatie\Permission\Models\Permission::where('name', $permission)->first();
    if ($permObj) {
        $customerRole->givePermissionTo($permObj);
        echo "Added permission: " . $permission . "\n";
    } else {
        echo "Permission not found: " . $permission . "\n";
    }
}

// Also update the other customer role (the one with ID 5)
$customRole = Spatie\Permission\Models\Role::find(5);
if ($customRole) {
    echo "\nUpdating permissions for role: " . $customRole->name . " (ID: " . $customRole->id . ")\n";
    
    // Assign permissions to the role
    foreach ($requiredPermissions as $permission) {
        $permObj = Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj) {
            $customRole->givePermissionTo($permObj);
            echo "Added permission: " . $permission . "\n";
        } else {
            echo "Permission not found: " . $permission . "\n";
        }
    }
}

echo "\nCustomer role permissions updated. New customers will have the correct permissions.\n";
