<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all customer users
$customers = \App\Models\User::where('type', 'customer')->get();

echo "Found " . $customers->count() . " customers\n";

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

// Process each customer
foreach ($customers as $customer) {
    echo "\nProcessing customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Make sure they have a role
    $userRole = \Spatie\Permission\Models\Role::where('name', 'customer')
        ->where('parent_id', $customer->parent_id)
        ->first();
        
    if (!$userRole) {
        echo "  - No customer role found with parent_id " . $customer->parent_id . "\n";
        $userRole = \Spatie\Permission\Models\Role::where('name', 'customer')->first();
        echo "  - Using default customer role: " . ($userRole ? $userRole->id : 'None found') . "\n";
    }
    
    if ($userRole) {
        // Assign role if not already assigned
        if (!$customer->hasRole($userRole)) {
            $customer->assignRole($userRole);
            echo "  - Assigned role: " . $userRole->name . " (ID: " . $userRole->id . ")\n";
        }
    }
    
    // Add permissions
    $addedCount = 0;
    foreach ($requiredPermissions as $permission) {
        $permObj = \Spatie\Permission\Models\Permission::where('name', $permission)->first();
        if ($permObj && !$customer->hasPermissionTo($permission)) {
            $customer->givePermissionTo($permObj);
            $addedCount++;
        }
    }
    
    echo "  - Added " . $addedCount . " missing permissions\n";
}

echo "\nAll customers have been updated with the correct permissions.\n";
