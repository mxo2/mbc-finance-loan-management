<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// First, we'll log in as Sam Panwar (owner)
$owner = \App\Models\User::where('email', 'abs.jaipur@gmail.com')->first();
\Auth::login($owner);

// Now, simulate creating a new customer
$userRole = \Spatie\Permission\Models\Role::where('parent_id', \Auth::user()->id)
    ->where('name', 'customer')
    ->first();

if (!$userRole) {
    echo "Error: Customer role not found for this owner\n";
    exit;
}

// Create a test customer
$testEmail = 'test_customer_'.time().'@example.com';
$customer = new \App\Models\User();
$customer->name = 'Test Customer ' . date('Y-m-d H:i:s');
$customer->email = $testEmail;
$customer->phone_number = '1234567890';
$customer->password = \Hash::make('password123');
$customer->type = $userRole->name;
$customer->email_verified_at = now();
$customer->lang = 'english';
$customer->parent_id = \Auth::user()->id;
$customer->save();

// Assign the role
$customer->assignRole($userRole);

// Apply our helper function to ensure permissions
\App\Helper\CustomerPermissions::ensurePermissions($customer);

echo "Created test customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
echo "Email: " . $customer->email . "\n";
echo "Role: " . $userRole->name . " (ID: " . $userRole->id . ")\n\n";

// Check permissions
echo "Checking permissions for new customer:\n";
echo "-------------------------\n";
$permissions = $customer->getAllPermissions()->pluck('name')->toArray();
sort($permissions);

echo "Total permissions: " . count($permissions) . "\n\n";

$requiredPermissions = [
    'manage loan', 
    'show loan', 
    'manage loan type', 
    'show loan type'
];

$missingPermissions = [];
foreach ($requiredPermissions as $perm) {
    if ($customer->can($perm)) {
        echo "✓ Has permission: " . $perm . "\n";
    } else {
        echo "✗ Missing permission: " . $perm . "\n";
        $missingPermissions[] = $perm;
    }
}

if (empty($missingPermissions)) {
    echo "\nTest PASSED: New customer has all required permissions.\n";
} else {
    echo "\nTest FAILED: New customer is missing permissions.\n";
}

// Clean up - delete the test customer
$customer->delete();
echo "\nTest customer deleted.\n";
