<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing customer portal access after fixes...\n";

// Get a test customer
$customer = \App\Models\User::where('type', 'customer')->first();

if (!$customer) {
    echo "❌ No customers found to test with.\n";
    exit;
}

echo "Testing with customer: {$customer->name} (ID: {$customer->id})\n";

// Test the conditions from the menu.blade.php file

// 1. Check if customer type check works
$isNotCustomer = $customer->type != 'customer';
echo "\n1. Customer type check:\n";
echo "   Customer type: {$customer->type}\n";
echo "   Is NOT customer: " . ($isNotCustomer ? "true" : "false") . "\n";
echo "   ✓ " . ($isNotCustomer ? "❌ FAIL - Should be false" : "✅ PASS - Customer type correctly detected") . "\n";

// 2. Check admin permissions that should trigger System Configuration section
$adminPermissions = [
    'manage notification',
    'manage loan type', 
    'manage document type',
    'manage account type'
];

echo "\n2. Admin permissions check:\n";
$hasAnyAdminPerm = false;
foreach ($adminPermissions as $permission) {
    $hasPermission = $customer->can($permission);
    $hasAnyAdminPerm = $hasAnyAdminPerm || $hasPermission;
    echo "   " . ($hasPermission ? "❌ HAS" : "✅ NO") . " $permission\n";
}

// 3. Overall menu condition
$shouldShowSystemConfig = $isNotCustomer && $hasAnyAdminPerm;
echo "\n3. Overall System Configuration visibility:\n";
echo "   Condition: (type != 'customer') AND (has admin permissions)\n";
echo "   Type check: " . ($isNotCustomer ? "true" : "false") . "\n";
echo "   Admin perms: " . ($hasAnyAdminPerm ? "true" : "false") . "\n";
echo "   Result: " . ($shouldShowSystemConfig ? "SHOW" : "HIDE") . "\n";
echo "   ✓ " . ($shouldShowSystemConfig ? "❌ FAIL - Should be hidden" : "✅ PASS - System Configuration will be hidden") . "\n";

// 4. Test individual menu items that should be hidden
echo "\n4. Individual admin menu items:\n";
$menuItems = [
    'manage branch' => 'Branch',
    'manage loan type' => 'Loan Type', 
    'manage document type' => 'Document Type',
    'manage account type' => 'Account Type',
    'manage notification' => 'Email Notification'
];

foreach ($menuItems as $permission => $menuName) {
    $hasPermission = $customer->can($permission);
    echo "   " . ($hasPermission ? "❌ CAN SEE" : "✅ HIDDEN") . " $menuName ($permission)\n";
}

// 5. Test customer-appropriate permissions
echo "\n5. Customer-appropriate permissions (should have):\n";
$customerPermissions = [
    'show loan' => 'View Loans',
    'create loan' => 'Apply for Loans',
    'show loan type' => 'View Loan Types',
    'manage contact' => 'Manage Contacts',
    'manage account settings' => 'Account Settings'
];

$missingPermissions = 0;
foreach ($customerPermissions as $permission => $description) {
    $hasPermission = $customer->can($permission);
    echo "   " . ($hasPermission ? "✅ HAS" : "❌ MISSING") . " $description ($permission)\n";
    if (!$hasPermission) $missingPermissions++;
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo "- Customer: {$customer->name}\n";
echo "- Type: {$customer->type}\n";
echo "- System Configuration section: " . ($shouldShowSystemConfig ? "❌ VISIBLE (BAD)" : "✅ HIDDEN (GOOD)") . "\n";
echo "- Admin menu items: " . ($hasAnyAdminPerm ? "❌ SOME VISIBLE (BAD)" : "✅ ALL HIDDEN (GOOD)") . "\n";
echo "- Customer permissions: " . ($missingPermissions == 0 ? "✅ ALL PRESENT (GOOD)" : "❌ $missingPermissions MISSING (BAD)") . "\n";

if (!$shouldShowSystemConfig && !$hasAnyAdminPerm && $missingPermissions == 0) {
    echo "\n🎉 SUCCESS! Customer portal is properly configured.\n";
    echo "   - Customers cannot see System Configuration section\n";
    echo "   - Customers cannot see Loan Type admin menu\n"; 
    echo "   - Customers have appropriate permissions for their role\n";
} else {
    echo "\n❌ ISSUES FOUND! Customer portal needs attention.\n";
}

echo "\n✅ Test completed.\n";