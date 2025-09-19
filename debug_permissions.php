<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'test@gmail.com')->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User ID: " . $user->id . "\n";
echo "User Name: " . $user->name . "\n";
echo "User Type: " . $user->type . "\n";
echo "Parent ID: " . $user->parent_id . "\n";
echo "Role ID: " . ($user->roles->first() ? $user->roles->first()->id : 'None') . "\n";
echo "Role Name: " . ($user->roles->first() ? $user->roles->first()->name : 'None') . "\n";
echo "\nPermissions:\n";
echo "-------------------------\n";

$permissions = $user->getAllPermissions()->pluck('name')->toArray();
sort($permissions);
foreach ($permissions as $permission) {
    echo "- " . $permission . "\n";
}

echo "\nCan access menu items:\n";
echo "-------------------------\n";
echo "manage loan: " . ($user->can('manage loan') ? 'Yes' : 'No') . "\n";
echo "manage loan type: " . ($user->can('manage loan type') ? 'Yes' : 'No') . "\n";
echo "manage contact: " . ($user->can('manage contact') ? 'Yes' : 'No') . "\n";
echo "manage note: " . ($user->can('manage note') ? 'Yes' : 'No') . "\n";
