<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing loan page resources and functionality...\n";

// Test 1: Check CSS file accessibility
echo "\n1. Testing CSS file:\n";
$cssPath = public_path('css/modern-loans.css');
if (file_exists($cssPath)) {
    $cssSize = filesize($cssPath);
    echo "   ✓ modern-loans.css exists ($cssSize bytes)\n";
    
    // Check if CSS contains key classes
    $cssContent = file_get_contents($cssPath);
    $keyClasses = ['.modern-loans-page', '.loan-card', '.btn-apply', '.hero-section'];
    foreach ($keyClasses as $class) {
        if (strpos($cssContent, $class) !== false) {
            echo "   ✓ CSS contains $class\n";
        } else {
            echo "   ❌ CSS missing $class\n";
        }
    }
} else {
    echo "   ❌ CSS file not found\n";
}

// Test 2: Check routes
echo "\n2. Testing routes:\n";
try {
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $testRoutes = [
        'loan' => 'GET /loan',
        'loan/wizard/{id}' => 'GET /loan/wizard/{loanTypeId}',
        'loan/apply/{id}' => 'GET /loan/apply/{loanTypeId}'
    ];
    
    foreach ($testRoutes as $pattern => $description) {
        $found = false;
        foreach ($routes as $route) {
            if (preg_match('#^' . str_replace('{id}', '[^/]+', $pattern) . '$#', $route->uri())) {
                echo "   ✓ $description exists\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "   ❌ $description not found\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Route check error: " . $e->getMessage() . "\n";
}

// Test 3: Check loan types data
echo "\n3. Testing loan types data:\n";
try {
    $loanTypes = \App\Models\LoanType::where('status', 1)->get();
    echo "   ✓ Found " . $loanTypes->count() . " active loan types\n";
    
    foreach ($loanTypes as $loanType) {
        echo "   - ID: {$loanType->id}, Type: {$loanType->type}, Min: {$loanType->minimum_amount}, Max: {$loanType->maximum_amount}\n";
    }
} catch (Exception $e) {
    echo "   ❌ Loan types error: " . $e->getMessage() . "\n";
}

// Test 4: Check view compilation
echo "\n4. Testing view compilation:\n";
try {
    $testUser = \App\Models\User::where('email', 'test@gmail.com')->first();
    if ($testUser) {
        \Illuminate\Support\Facades\Auth::login($testUser);
        
        $controller = new \App\Http\Controllers\LoanApplicationController();
        $response = $controller->index();
        
        if ($response instanceof \Illuminate\View\View) {
            // Try to render the view
            $viewContent = $response->render();
            
            // Check for key elements
            $checks = [
                'modern-loans-page' => 'Main wrapper class',
                'loan-card' => 'Loan cards',
                'btn-apply' => 'Apply buttons',
                'onclick="applyNow(' => 'Apply button JavaScript',
                'font-awesome' => 'Font Awesome icons'
            ];
            
            foreach ($checks as $search => $description) {
                if (strpos($viewContent, $search) !== false) {
                    echo "   ✓ View contains $description\n";
                } else {
                    echo "   ❌ View missing $description\n";
                }
            }
            
            echo "   ✓ View renders successfully\n";
        } else {
            echo "   ❌ Controller doesn't return view\n";
        }
    } else {
        echo "   ❌ Test user not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ View compilation error: " . $e->getMessage() . "\n";
}

// Test 5: Generate sample JavaScript for debugging
echo "\n5. Generating JavaScript debug code:\n";
$debugJS = "
// Add this to browser console to test functionality
console.log('Testing loan page functions...');

// Test if functions exist
const functions = ['applyNow', 'exploreLoan', 'startApplication'];
functions.forEach(func => {
    if (typeof window[func] === 'function') {
        console.log('✓ Function ' + func + ' exists');
    } else {
        console.log('❌ Function ' + func + ' missing');
    }
});

// Test CSS loading
const modernPage = document.querySelector('.modern-loans-page');
if (modernPage) {
    const styles = window.getComputedStyle(modernPage);
    console.log('✓ Modern page element found');
    console.log('Font family:', styles.fontFamily);
    console.log('Background:', styles.background);
} else {
    console.log('❌ Modern page element not found');
}

// Test loan cards
const loanCards = document.querySelectorAll('.loan-card');
console.log('Found ' + loanCards.length + ' loan cards');

// Test buttons
const applyButtons = document.querySelectorAll('.btn-apply');
console.log('Found ' + applyButtons.length + ' apply buttons');
";

file_put_contents(public_path('debug-loan-page.js'), $debugJS);
echo "   ✓ Debug JavaScript saved to public/debug-loan-page.js\n";
echo "   ℹ️  Add this to browser console: " . url('debug-loan-page.js') . "\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "LOAN PAGE TESTING COMPLETE\n";
echo "\nNext steps:\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Verify CSS is loading in Network tab\n";
echo "3. Test apply buttons manually\n";
echo "4. Check for admin layout conflicts\n";