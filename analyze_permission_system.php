<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Let's check if there's a permission inheritance mechanism
echo "Checking permission inheritance\n";
echo "==============================\n\n";

// Let's check how hasPermissionTo is implemented
echo "Testing hasPermissionTo implementation for a customer role:\n";
$role = \Spatie\Permission\Models\Role::where('name', 'customer')->first();

// Get direct permissions
$directPermissions = $role->permissions()->pluck('name')->toArray();
echo "Direct permissions for role:\n";
echo implode(', ', $directPermissions) . "\n\n";

// Test for a specific permission
$testPermissions = [
    'create loan type',
    'edit loan type',
    'delete loan type',
    'manage loan type',
    'edit loan',
    'delete loan',
    'show loan type',
    'manage loan'
];

foreach ($testPermissions as $permission) {
    // Check if role has permission directly
    $hasDirectly = in_array($permission, $directPermissions);
    
    // Check using hasPermissionTo
    $hasPermission = $role->hasPermissionTo($permission);
    
    echo "Permission '$permission':\n";
    echo "  - Direct permission check: " . ($hasDirectly ? "Yes" : "No") . "\n";
    echo "  - hasPermissionTo() result: " . ($hasPermission ? "Yes" : "No") . "\n";
    echo "\n";
}

// Now test a customer user
echo "Testing hasPermissionTo implementation for a customer user:\n";
$customer = \App\Models\User::where('type', 'customer')->first();

// Get direct permissions
$directPermissions = $customer->permissions()->pluck('name')->toArray();
echo "Direct permissions for user:\n";
echo implode(', ', $directPermissions) . "\n\n";

// Get permissions via roles
$rolePermissions = collect([]);
foreach ($customer->roles as $role) {
    $rolePermissions = $rolePermissions->merge($role->permissions()->pluck('name'));
}
$rolePermissions = $rolePermissions->unique()->toArray();

echo "Permissions via roles:\n";
echo implode(', ', $rolePermissions) . "\n\n";

foreach ($testPermissions as $permission) {
    // Check if user has permission directly
    $hasDirectly = in_array($permission, $directPermissions);
    
    // Check if user has permission via roles
    $hasViaRoles = in_array($permission, $rolePermissions);
    
    // Check using hasPermissionTo
    $hasPermission = $customer->hasPermissionTo($permission);
    
    echo "Permission '$permission':\n";
    echo "  - Direct permission check: " . ($hasDirectly ? "Yes" : "No") . "\n";
    echo "  - Via roles check: " . ($hasViaRoles ? "Yes" : "No") . "\n";
    echo "  - hasPermissionTo() result: " . ($hasPermission ? "Yes" : "No") . "\n";
    echo "\n";
}

// Look for any custom permission logic in the User model
echo "Checking for custom permission logic in User model:\n";
$userModel = new \ReflectionClass(\App\Models\User::class);
$methods = $userModel->getMethods(\ReflectionMethod::IS_PUBLIC);

$permissionMethods = [];
foreach ($methods as $method) {
    if (strpos($method->getName(), 'permission') !== false || 
        strpos($method->getName(), 'Permission') !== false ||
        strpos($method->getName(), 'can') !== false ||
        strpos($method->getName(), 'Can') !== false) {
        $permissionMethods[] = $method->getName();
    }
}

if (count($permissionMethods) > 0) {
    echo "Found permission-related methods in User model:\n";
    echo implode(", ", $permissionMethods) . "\n\n";
} else {
    echo "No custom permission methods found in User model.\n\n";
}

// Check if there's a parent-child relationship in permissions
echo "Checking for parent-child relationships in permissions:\n";
$permissions = \Spatie\Permission\Models\Permission::all();
$permissionsWithParent = $permissions->filter(function($permission) {
    return !empty($permission->parent_id);
})->count();

echo "Permissions with parent_id set: $permissionsWithParent out of " . $permissions->count() . "\n\n";

// Check for wildcards or pattern-based permissions
echo "Checking for wildcard permissions handling:\n";
if (method_exists(\Spatie\Permission\PermissionRegistrar::class, 'getWildcardPermissions')) {
    echo "Spatie supports wildcard permissions.\n";
} else {
    echo "No built-in wildcard permissions support found.\n";
}

echo "\nPermission system analysis complete.\n";