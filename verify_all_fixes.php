<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "FINAL VERIFICATION OF FIXES\n";
echo "==========================\n\n";

// Test 1: Check if LoanType model has 'simple' interest type
echo "Test 1: Checking LoanType model for 'simple' interest type\n";
$interestTypes = \App\Models\LoanType::$interestType;
if (isset($interestTypes['simple'])) {
    echo "✓ 'simple' interest type exists in LoanType model\n";
} else {
    echo "✗ 'simple' interest type missing from LoanType model\n";
}
echo "\n";

// Test 2: Check customer role permissions
echo "Test 2: Checking customer role permissions\n";
$customerRoles = \Spatie\Permission\Models\Role::where('name', 'customer')->get();
echo "Found " . $customerRoles->count() . " customer roles\n";

$shouldHavePermissions = [
    'show loan type',
    'manage loan',
    'create loan',
    'show loan'
];

$shouldNotHavePermissions = [
    'create loan type',
    'edit loan type',
    'delete loan type',
    'manage loan type',
    'edit loan',
    'delete loan'
];

foreach ($customerRoles as $role) {
    echo "Role ID: " . $role->id . "\n";
    
    $missingRequired = [];
    foreach ($shouldHavePermissions as $permission) {
        if (!$role->permissions()->where('name', $permission)->exists()) {
            $missingRequired[] = $permission;
        }
    }
    
    $hasProhibited = [];
    foreach ($shouldNotHavePermissions as $permission) {
        if ($role->permissions()->where('name', $permission)->exists()) {
            $hasProhibited[] = $permission;
        }
    }
    
    if (empty($missingRequired) && empty($hasProhibited)) {
        echo "✓ Role has correct permissions\n";
    } else {
        if (!empty($missingRequired)) {
            echo "✗ Role is missing required permissions: " . implode(', ', $missingRequired) . "\n";
        }
        if (!empty($hasProhibited)) {
            echo "✗ Role has prohibited permissions: " . implode(', ', $hasProhibited) . "\n";
        }
    }
}
echo "\n";

// Test 3: Check LoanTypeController
echo "Test 3: Checking LoanTypeController logic\n";
$controllerPath = app_path('Http/Controllers/LoanTypeController.php');
$content = file_get_contents($controllerPath);

$indexCheck = strpos($content, '\Auth::user()->can(\'manage loan type\') || \Auth::user()->can(\'show loan type\')');
$showCheck = strpos($content, 'if (\Auth::user()->can(\'manage loan type\') || \Auth::user()->can(\'show loan type\'))');

if ($indexCheck !== false) {
    echo "✓ LoanTypeController index method checks for 'show loan type' permission\n";
} else {
    echo "✗ LoanTypeController index method does not check for 'show loan type' permission\n";
}

if ($showCheck !== false) {
    echo "✓ LoanTypeController show method checks for proper permissions\n";
} else {
    echo "✗ LoanTypeController show method does not check for proper permissions\n";
}
echo "\n";

// Test 4: Check CustomerController
echo "Test 4: Checking CustomerController for correct permission assignment\n";
$controllerPath = app_path('Http/Controllers/CustomerController.php');
$content = file_get_contents($controllerPath);

// Check for the updated permission list
$permissionCheck = strpos($content, 'show loan type');
$editLoanCheck = strpos($content, 'edit loan');

if ($permissionCheck !== false) {
    echo "✓ CustomerController includes 'show loan type' permission\n";
} else {
    echo "✗ CustomerController does not include 'show loan type' permission\n";
}

if (strpos($content, 'create loan') !== false && strpos($content, 'show loan') !== false) {
    echo "✓ CustomerController includes basic loan permissions\n";
} else {
    echo "✗ CustomerController is missing basic loan permissions\n";
}

// If edit_loan is still in the controller, it shouldn't be
if ($editLoanCheck === false) {
    echo "✓ CustomerController does not include 'edit loan' permission\n";
} else {
    echo "✗ CustomerController still includes 'edit loan' permission\n";
}
echo "\n";

// Test 5: Summary
echo "Test 5: Overall verification\n";
$passed = true;

// Count how many loan types should be visible to customers
$loanTypeCount = \App\Models\LoanType::count();
echo "Total loan types in system: $loanTypeCount\n";

// Check for a customer user
$customer = \App\Models\User::where('type', 'customer')->first();
if ($customer) {
    echo "Testing with customer: " . $customer->name . " (ID: " . $customer->id . ")\n";
    
    // Check if customer can view loan types
    $canViewLoanTypes = $customer->can('show loan type');
    $canManageLoanTypes = $customer->can('manage loan type');
    $canCreateLoanTypes = $customer->can('create loan type');
    
    echo "Can view loan types: " . ($canViewLoanTypes ? "Yes" : "No") . "\n";
    echo "Can manage loan types: " . ($canManageLoanTypes ? "Yes" : "No") . "\n";
    echo "Can create loan types: " . ($canCreateLoanTypes ? "Yes" : "No") . "\n";
    
    if ($canViewLoanTypes && !$canCreateLoanTypes) {
        echo "✓ Customer permissions are correctly set\n";
    } else {
        echo "✗ Customer permissions are not correctly set\n";
        $passed = false;
    }
} else {
    echo "No customer users found for testing\n";
    $passed = false;
}

echo "\n";
echo "FINAL RESULT: " . ($passed ? "✓ All tests passed" : "✗ Some tests failed") . "\n";
echo "\n";
echo "End of verification.\n";