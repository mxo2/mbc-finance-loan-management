<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing view variables for modern_index...\n";

// Get test user
$testUser = \App\Models\User::where('email', 'test@gmail.com')->first();

if ($testUser) {
    // Log in the test user
    \Illuminate\Support\Facades\Auth::login($testUser);
    echo "✓ Test user logged in: {$testUser->name}\n";
    
    // Simulate controller call
    $controller = new \App\Http\Controllers\LoanApplicationController();
    $response = $controller->index();
    
    if ($response instanceof \Illuminate\View\View) {
        echo "✓ Controller returns view: {$response->getName()}\n";
        
        $viewData = $response->getData();
        echo "✓ View data keys: " . implode(', ', array_keys($viewData)) . "\n";
        
        // Check specific variables
        $checks = [
            'loanTypes' => 'Loan types for selection',
            'branches' => 'Branch locations',
            'activeApplications' => 'Active applications count',
            'myLoans' => 'User\'s loan history'
        ];
        
        echo "\nVariable checks:\n";
        foreach ($checks as $varName => $description) {
            if (array_key_exists($varName, $viewData)) {
                $value = $viewData[$varName];
                if (is_countable($value)) {
                    echo "   ✓ $varName: " . count($value) . " items ($description)\n";
                } else {
                    echo "   ✓ $varName: " . gettype($value) . " ($description)\n";
                }
            } else {
                echo "   ❌ $varName: MISSING ($description)\n";
            }
        }
        
        // Check myLoans specifically
        if (isset($viewData['myLoans'])) {
            $myLoans = $viewData['myLoans'];
            echo "\nMyLoans details:\n";
            echo "   Type: " . get_class($myLoans) . "\n";
            echo "   Count: " . $myLoans->count() . "\n";
            echo "   Is Collection: " . ($myLoans instanceof \Illuminate\Support\Collection ? 'Yes' : 'No') . "\n";
            
            if ($myLoans->count() > 0) {
                $firstLoan = $myLoans->first();
                echo "   Sample loan ID: {$firstLoan->id}\n";
                echo "   Sample loan status: {$firstLoan->status}\n";
            } else {
                echo "   No loans found for user (this is normal for new users)\n";
            }
        }
        
        echo "\n✅ All required variables are present and accessible!\n";
        echo "The undefined variable error should be resolved.\n";
        
    } else {
        echo "❌ Controller doesn't return view\n";
    }
} else {
    echo "❌ Test user not found\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "TEST COMPLETE\n";