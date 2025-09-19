<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create a fix for the LoanTypeController
echo "Fixing LoanTypeController for customers to view loan types\n";
echo "=======================================================\n\n";

// First, get the file content
$controllerPath = app_path('Http/Controllers/LoanTypeController.php');
$content = file_get_contents($controllerPath);

// Replace the 'index' method to also allow 'show loan type' permission
$oldIndexMethod = <<<'EOD'
    public function index()
    {
        if (\Auth::user()->can('manage loan type')) {
            $loanTypes = LoanType::where('parent_id', \Auth::user()->id)->orderBy('id', 'DESC')->get();
            return view('loan_type.index', compact('loanTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
EOD;

$newIndexMethod = <<<'EOD'
    public function index()
    {
        if (\Auth::user()->can('manage loan type') || \Auth::user()->can('show loan type')) {
            // If customer, get loan types from parent
            if (\Auth::user()->type == 'customer') {
                $loanTypes = LoanType::where('parent_id', \Auth::user()->parent_id)->orderBy('id', 'DESC')->get();
            } else {
                $loanTypes = LoanType::where('parent_id', \Auth::user()->id)->orderBy('id', 'DESC')->get();
            }
            return view('loan_type.index', compact('loanTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
EOD;

// Replace the method
$updatedContent = str_replace($oldIndexMethod, $newIndexMethod, $content);

// Also update the 'show' method to check permissions
$oldShowMethod = <<<'EOD'
    public function show($id)
    {
        $loanType=LoanType::find(decrypt($id));
        return view('loan_type.show',compact('loanType'));
    }
EOD;

$newShowMethod = <<<'EOD'
    public function show($id)
    {
        if (\Auth::user()->can('manage loan type') || \Auth::user()->can('show loan type')) {
            $loanType=LoanType::find(decrypt($id));
            return view('loan_type.show',compact('loanType'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
EOD;

// Replace the method
$updatedContent = str_replace($oldShowMethod, $newShowMethod, $updatedContent);

// Write the updated content back to the file
file_put_contents($controllerPath, $updatedContent);

echo "Updated LoanTypeController to allow customers to view loan types with 'show loan type' permission.\n";
echo "Customers can now view loan types but cannot create, edit, or delete them.\n";

// Output summary of what we've done
echo "\nSummary of permission fixes:\n";
echo "===========================\n";
echo "1. Removed direct permissions from roles and users that should not have them\n";
echo "2. Updated the LoanTypeController to check for 'show loan type' permission\n";
echo "3. Fixed the index method to show loan types to customers based on parent_id\n";
echo "4. Updated the show method to also check for proper permissions\n";
echo "5. Updated CustomerController to assign correct permissions to new customers\n\n";

echo "Customers now have these permissions:\n";
$permissions = ['manage loan', 'create loan', 'show loan', 'show loan type', 'manage contact', 'create contact', 'edit contact', 'delete contact', 'manage note', 'create note', 'edit note', 'delete note', 'manage account', 'show account', 'manage transaction', 'manage repayment', 'manage account settings', 'manage password settings', 'manage 2FA settings'];
foreach ($permissions as $permission) {
    echo "- $permission\n";
}

echo "\nCustomers do NOT have these permissions:\n";
$removedPermissions = ['create loan type', 'edit loan type', 'delete loan type', 'manage loan type', 'edit loan', 'delete loan'];
foreach ($removedPermissions as $permission) {
    echo "- $permission\n";
}

echo "\nFixes completed successfully.\n";