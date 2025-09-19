<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get all the permission IDs
$permissionMap = [];
$permissions = \Spatie\Permission\Models\Permission::all();
foreach ($permissions as $permission) {
    $permissionMap[$permission->id] = $permission->name;
}

// Get all customer roles
$customerRoles = \Spatie\Permission\Models\Role::where('name', 'customer')->get();

echo "Direct DB query of role_has_permissions table:\n";
echo "===========================================\n\n";

foreach ($customerRoles as $role) {
    echo "Role: " . $role->name . " (ID: " . $role->id . ")\n";
    
    // Query the pivot table directly
    $rows = DB::table('role_has_permissions')
        ->where('role_id', $role->id)
        ->get();
    
    echo "  Permission mappings in role_has_permissions table:\n";
    foreach ($rows as $row) {
        $permId = $row->permission_id;
        $permName = isset($permissionMap[$permId]) ? $permissionMap[$permId] : "Unknown";
        echo "    - " . $permName . " (ID: " . $permId . ")\n";
    }
    echo "\n";
}

// Now check individual customers
$customers = \App\Models\User::where('type', 'customer')->get();

echo "Direct DB query of model_has_permissions table:\n";
echo "============================================\n\n";

foreach ($customers as $customer) {
    echo "Customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Query the pivot table directly
    $rows = DB::table('model_has_permissions')
        ->where('model_id', $customer->id)
        ->where('model_type', get_class($customer))
        ->get();
    
    echo "  Permission mappings in model_has_permissions table:\n";
    foreach ($rows as $row) {
        $permId = $row->permission_id;
        $permName = isset($permissionMap[$permId]) ? $permissionMap[$permId] : "Unknown";
        echo "    - " . $permName . " (ID: " . $permId . ")\n";
    }
    
    // Check role assignments
    $roleRows = DB::table('model_has_roles')
        ->where('model_id', $customer->id)
        ->where('model_type', get_class($customer))
        ->get();
    
    echo "  Role mappings in model_has_roles table:\n";
    foreach ($roleRows as $row) {
        $roleId = $row->role_id;
        $role = \Spatie\Permission\Models\Role::find($roleId);
        $roleName = $role ? $role->name : "Unknown";
        echo "    - " . $roleName . " (ID: " . $roleId . ")\n";
    }
    echo "\n";
}

echo "DB query verification complete.\n";