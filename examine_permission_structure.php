<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Let's check the roles and permissions structure
echo "Examining Spatie Permission Structure\n";
echo "====================================\n\n";

// Check roles table
echo "Roles Table Structure:\n";
$roles = \Spatie\Permission\Models\Role::all();
echo "Total Roles: " . $roles->count() . "\n";
foreach ($roles as $role) {
    echo "Role: " . $role->name . " (ID: " . $role->id . ")\n";
    echo "  - guard_name: " . $role->guard_name . "\n";
    echo "  - parent_id: " . $role->parent_id . "\n";
    echo "\n";
}

// Check permissions table
echo "Permissions Table Structure:\n";
$permissions = \Spatie\Permission\Models\Permission::all();
echo "Total Permissions: " . $permissions->count() . "\n";
echo "First 10 permissions:\n";
$count = 0;
foreach ($permissions as $permission) {
    echo "Permission: " . $permission->name . " (ID: " . $permission->id . ")\n";
    echo "  - guard_name: " . $permission->guard_name . "\n";
    echo "  - parent_id: " . $permission->parent_id . "\n";
    echo "\n";
    $count++;
    if ($count >= 10) {
        echo "... and " . ($permissions->count() - 10) . " more permissions\n\n";
        break;
    }
}

// Check role_has_permissions pivot table
echo "Role-Permission Mappings for Customer Roles:\n";
$customerRoles = \Spatie\Permission\Models\Role::where('name', 'customer')->get();
foreach ($customerRoles as $role) {
    echo "Role: " . $role->name . " (ID: " . $role->id . ")\n";
    $permissionIds = DB::table('role_has_permissions')
        ->where('role_id', $role->id)
        ->pluck('permission_id')
        ->toArray();
    
    echo "  Total permissions: " . count($permissionIds) . "\n";
    echo "  Permission IDs: " . implode(', ', $permissionIds) . "\n";
    
    // Get permission names for these IDs
    $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)
        ->pluck('name', 'id')
        ->toArray();
    
    echo "  Permissions:\n";
    foreach ($permissionNames as $id => $name) {
        echo "    - " . $name . " (ID: " . $id . ")\n";
    }
    echo "\n";
}

// Check model_has_permissions pivot table for direct permissions
echo "Direct User-Permission Mappings for Customer Users:\n";
$customers = \App\Models\User::where('type', 'customer')->get();
foreach ($customers as $customer) {
    echo "Customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    $permissionIds = DB::table('model_has_permissions')
        ->where('model_id', $customer->id)
        ->where('model_type', get_class($customer))
        ->pluck('permission_id')
        ->toArray();
    
    echo "  Total direct permissions: " . count($permissionIds) . "\n";
    if (count($permissionIds) > 0) {
        echo "  Permission IDs: " . implode(', ', $permissionIds) . "\n";
        
        // Get permission names for these IDs
        $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)
            ->pluck('name', 'id')
            ->toArray();
        
        echo "  Permissions:\n";
        foreach ($permissionNames as $id => $name) {
            echo "    - " . $name . " (ID: " . $id . ")\n";
        }
    }
    echo "\n";
}

// Check model_has_roles pivot table
echo "User-Role Mappings for Customer Users:\n";
foreach ($customers as $customer) {
    echo "Customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    $roleIds = DB::table('model_has_roles')
        ->where('model_id', $customer->id)
        ->where('model_type', get_class($customer))
        ->pluck('role_id')
        ->toArray();
    
    echo "  Total roles: " . count($roleIds) . "\n";
    if (count($roleIds) > 0) {
        echo "  Role IDs: " . implode(', ', $roleIds) . "\n";
        
        // Get role names for these IDs
        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $roleIds)
            ->pluck('name', 'id')
            ->toArray();
        
        echo "  Roles:\n";
        foreach ($roleNames as $id => $name) {
            echo "    - " . $name . " (ID: " . $id . ")\n";
        }
    }
    echo "\n";
}

// Examine Permission Caching
echo "Permission Caching Information:\n";
echo "  - Check if cache is enabled: " . (config('permission.cache.enabled') ? 'Yes' : 'No') . "\n";
echo "  - Cache expiration time: " . config('permission.cache.expiration_time', 'N/A') . " minutes\n";
echo "  - Cache key: " . config('permission.cache.key', 'N/A') . "\n";
echo "  - Cache store: " . config('permission.cache.store', 'default') . "\n";

echo "\nExamination complete.\n";