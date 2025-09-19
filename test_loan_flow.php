<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing loan application flow...\n";

// Test 1: Check if the route exists
echo "\n1. Testing route registration:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $loanRoute = null;
    foreach ($routes as $route) {
        if ($route->uri() === 'loan' && in_array('GET', $route->methods())) {
            $loanRoute = $route;
            break;
        }
    }
    
    if ($loanRoute) {
        echo "   ✓ Route 'GET /loan' is registered\n";
        echo "   ✓ Controller: " . $loanRoute->getActionName() . "\n";
    } else {
        echo "   ❌ Route 'GET /loan' not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error checking routes: " . $e->getMessage() . "\n";
}

// Test 2: Check if LoanApplicationController exists
echo "\n2. Testing controller:\n";
try {
    $controller = new \App\Http\Controllers\LoanApplicationController();
    echo "   ✓ LoanApplicationController exists\n";
    
    if (method_exists($controller, 'index')) {
        echo "   ✓ index() method exists\n";
    } else {
        echo "   ❌ index() method missing\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller error: " . $e->getMessage() . "\n";
}

// Test 3: Check if view file exists
echo "\n3. Testing view file:\n";
$viewPath = resource_path('views/loans/modern_index.blade.php');
if (file_exists($viewPath)) {
    echo "   ✓ View file exists: loans.modern_index\n";
    $viewSize = filesize($viewPath);
    echo "   ✓ View file size: " . number_format($viewSize) . " bytes\n";
} else {
    echo "   ❌ View file missing: $viewPath\n";
}

// Test 4: Check if CSS files exist
echo "\n4. Testing CSS files:\n";
$cssFiles = [
    'modern-loans.css' => public_path('css/modern-loans.css'),
    'loan-wizard.css' => public_path('css/loan-wizard.css')
];

foreach ($cssFiles as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "   ✓ $name exists (" . number_format($size) . " bytes)\n";
    } else {
        echo "   ❌ $name missing at $path\n";
    }
}

// Test 5: Check database connectivity and loan types
echo "\n5. Testing database:\n";
try {
    $loanTypesCount = \App\Models\LoanType::count();
    echo "   ✓ Database connection working\n";
    echo "   ✓ Total loan types in database: $loanTypesCount\n";
    
    $activeLoanTypes = \App\Models\LoanType::where('status', 1)->count();
    echo "   ✓ Active loan types: $activeLoanTypes\n";
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 6: Check if test user exists
echo "\n6. Testing user authentication:\n";
try {
    $testUser = \App\Models\User::where('email', 'test@gmail.com')->first();
    if ($testUser) {
        echo "   ✓ Test user exists: {$testUser->name} ({$testUser->email})\n";
        echo "   ✓ User type: {$testUser->type}\n";
        
        // Check parent_id for loan types visibility
        if ($testUser->parent_id) {
            $parentLoanTypes = \App\Models\LoanType::where('parent_id', $testUser->parent_id)->where('status', 1)->count();
            echo "   ✓ Loan types visible to user: $parentLoanTypes\n";
        }
    } else {
        echo "   ❌ Test user (test@gmail.com) not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ User check error: " . $e->getMessage() . "\n";
}

// Test 7: Check parentId() function
echo "\n7. Testing parentId() function:\n";
try {
    if (function_exists('parentId')) {
        echo "   ✓ parentId() function exists\n";
        
        // Try to get parent ID for test user
        if (isset($testUser)) {
            $parentId = $testUser->parent_id ?: $testUser->id;
            echo "   ✓ Parent ID for test user: $parentId\n";
        }
    } else {
        echo "   ❌ parentId() function not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ parentId() function error: " . $e->getMessage() . "\n";
}

// Test 8: Simulate controller call
echo "\n8. Testing controller simulation:\n";
try {
    // Set up basic authentication simulation
    if (isset($testUser)) {
        \Illuminate\Support\Facades\Auth::login($testUser);
        echo "   ✓ Test user logged in\n";
        
        $controller = new \App\Http\Controllers\LoanApplicationController();
        $response = $controller->index();
        
        if ($response instanceof \Illuminate\View\View) {
            echo "   ✓ Controller returns view\n";
            echo "   ✓ View name: " . $response->getName() . "\n";
            
            $viewData = $response->getData();
            echo "   ✓ View data keys: " . implode(', ', array_keys($viewData)) . "\n";
            
            if (isset($viewData['loanTypes'])) {
                echo "   ✓ Loan types passed to view: " . count($viewData['loanTypes']) . "\n";
            }
        } else {
            echo "   ❌ Controller doesn't return view\n";
        }
    } else {
        echo "   ⚠️  Skipped - no test user available\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller simulation error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "LOAN APPLICATION FLOW TEST COMPLETE\n";
echo "If all tests pass, the loan application should work correctly.\n";
echo "If there are failures, please address them before testing in browser.\n";