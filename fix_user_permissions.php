<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'test@gmail.com')->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

// Get the customer role
$customerRole = Spatie\Permission\Models\Role::where('name', 'customer')->first();
if (!$customerRole) {
    echo "Customer role not found\n";
    exit;
}

// Re-assign the role to the user
$user->syncRoles([$customerRole]);

// Get all permissions for loan management
$permissions = [
    'manage loan', 
    'create loan', 
    'edit loan', 
    'delete loan', 
    'show loan',
    'manage loan type', 
    'create loan type', 
    'edit loan type', 
    'delete loan type', 
    'show loan type'
];

// Assign permissions directly to the user
foreach ($permissions as $permission) {
    $permObj = Spatie\Permission\Models\Permission::where('name', $permission)->first();
    if ($permObj) {
        $user->givePermissionTo($permObj);
        echo "Added permission: " . $permission . "\n";
    } else {
        echo "Permission not found: " . $permission . "\n";
    }
}

echo "\nPermissions updated for user " . $user->name . "\n";
