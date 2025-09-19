<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\LoanCycleController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\ModernLandingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\TransactionController;
use App\Models\RepaymentSchedule;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Main Laravel Web Application Routes
require __DIR__ . '/auth.php';

// Main website routes - Serve React frontend
Route::get('/', function () {
    // Check if we have a built frontend to serve
    $frontendPath = base_path('public/frontend/index.html');
    
    // Fallback to development path if production build doesn't exist
    if (!file_exists($frontendPath)) {
        $frontendPath = base_path('FinanceFlow/FinanceFlow/dist/public/index.html');
    }
    
    if (file_exists($frontendPath)) {
        $content = file_get_contents($frontendPath);
        return response($content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    } else {
        // Simple fallback without using view() to avoid service container issues
        $fallbackHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBC Finance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 2rem; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .links { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 MBC Finance</h1>
        <p>Welcome to our Financial Services Platform</p>
        <div class="links">
            <a href="/dashboard" class="btn">Admin Dashboard</a>
            <a href="/loan" class="btn">Loan Application</a>
            <a href="/login" class="btn">Login</a>
            <a href="/register" class="btn">Register</a>
        </div>
        <p style="margin-top: 2rem; text-align: center; color: #666;">
            Frontend is being updated. Use the links above to access services.
        </p>
    </div>
</body>
</html>';
        
        return response($fallbackHtml, 200, [
            'Content-Type' => 'text/html; charset=UTF-8'
        ]);
    }
});

// Serve frontend assets - with fallback to development location
Route::get('/assets/{file}', function ($file) {
    // Try production location first
    $assetPath = base_path('public/frontend/assets/' . $file);
    
    // Fallback to development location
    if (!file_exists($assetPath)) {
        $assetPath = base_path('FinanceFlow/FinanceFlow/dist/public/assets/' . $file);
    }
    
    if (file_exists($assetPath)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $mimeType = match($extension) {
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'woff', 'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            default => 'application/octet-stream'
        };
        
        return response(file_get_contents($assetPath))
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
    
    abort(404);
})->where('file', '.*');

// PWA Routes - Handled by React application
Route::prefix('pwa')->group(function () {
    Route::get('{any}', function () {
        return file_get_contents(public_path('pwa/index.html'));
    })->where('any', '.*');
});

// Frontend homepage route
Route::get('/frontend', [App\Http\Controllers\FrontPageController::class, 'index'])->name('frontend.homepage');

Route::post('/apply', [App\Http\Controllers\FrontPageController::class, 'applyLoan'])->name('front.apply')->middleware(
    [
        'XSS',
    ]
);

Route::post('/calculate-emi', [App\Http\Controllers\FrontPageController::class, 'calculateEMI'])->name('front.calculate-emi')->middleware(
    [
        'XSS',
    ]
);
Route::get('home', [HomeController::class, 'index'])->name('home')->middleware(
    [

        'XSS',
    ]
);
Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware(
    [

        'XSS',
    ]
);

//-------------------------------User-------------------------------------------

Route::resource('users', UserController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::get('setauth/{id}',  function ($id) {
    $user = User::find($id);
    \Illuminate\Support\Facades\Auth::login($user);
    return redirect()->route('home');
});


Route::get('login/otp', [OTPController::class, 'show'])->name('otp.show')->middleware(
    [

        'XSS',
    ]
);
Route::post('login/otp', [OTPController::class, 'check'])->name('otp.check')->middleware(
    [

        'XSS',
    ]
);
Route::get('login/2fa/disable', [OTPController::class, 'disable'])->name('2fa.disable')->middleware(['XSS',]);

//-------------------------------Subscription-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::resource('subscriptions', SubscriptionController::class);
        Route::get('coupons/history', [CouponController::class, 'history'])->name('coupons.history');
        Route::delete('coupons/history/{id}/destroy', [CouponController::class, 'historyDestroy'])->name('coupons.history.destroy');
        Route::get('coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
        Route::resource('coupons', CouponController::class);
        Route::get('subscription/transaction', [SubscriptionController::class, 'transaction'])->name('subscription.transaction');
    }
);

//-------------------------------Subscription Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('subscription/{id}/stripe/payment', [SubscriptionController::class, 'stripePayment'])->name('subscription.stripe.payment');
    }
);
//-------------------------------Settings-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('settings', [SettingController::class, 'index'])->name('setting.index');

        Route::post('settings/account', [SettingController::class, 'accountData'])->name('setting.account');
        Route::delete('settings/account/delete', [SettingController::class, 'accountDelete'])->name('setting.account.delete');
        Route::post('settings/password', [SettingController::class, 'passwordData'])->name('setting.password');
        Route::post('settings/general', [SettingController::class, 'generalData'])->name('setting.general');
        Route::post('settings/smtp', [SettingController::class, 'smtpData'])->name('setting.smtp');
        Route::get('settings/smtp-test', [SettingController::class, 'smtpTest'])->name('setting.smtp.test');
        Route::post('settings/smtp-test', [SettingController::class, 'smtpTestMailSend'])->name('setting.smtp.testing');
        Route::post('settings/payment', [SettingController::class, 'paymentData'])->name('setting.payment');
        Route::post('settings/site-seo', [SettingController::class, 'siteSEOData'])->name('setting.site.seo');
        Route::post('settings/google-recaptcha', [SettingController::class, 'googleRecaptchaData'])->name('setting.google.recaptcha');
        Route::post('settings/company', [SettingController::class, 'companyData'])->name('setting.company');
        Route::post('settings/2fa', [SettingController::class, 'twofaEnable'])->name('setting.twofa.enable');

        Route::get('footer-setting', [SettingController::class, 'footerSetting'])->name('footerSetting');
        Route::post('settings/footer', [SettingController::class, 'footerData'])->name('setting.footer');

        Route::get('language/{lang}', [SettingController::class, 'lanquageChange'])->name('language.change');
        Route::post('theme/settings', [SettingController::class, 'themeSettings'])->name('theme.settings');

        Route::post('storage/settings', [SettingController::class, 'storageSetting'])->name('storage.setting');
        Route::post('settings/twilio', [SettingController::class, 'twilio'])->name('setting.twilio');
        
    }
);


//-------------------------------Role & Permissions-------------------------------------------
Route::resource('permission', PermissionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::resource('role', RoleController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Note-------------------------------------------
Route::resource('note', NoticeBoardController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Contact-------------------------------------------
Route::resource('contact', ContactController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------logged History-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::get('logged/history', [UserController::class, 'loggedHistory'])->name('logged.history');
        Route::get('logged/{id}/history/show', [UserController::class, 'loggedHistoryShow'])->name('logged.history.show');
        Route::delete('logged/{id}/history', [UserController::class, 'loggedHistoryDestroy'])->name('logged.history.destroy');
    }
);


//-------------------------------Plan Payment-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
        Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
        Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
        Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
        Route::post('subscription/{id}/{user_id}/manual-assign-package', [PaymentController::class, 'subscriptionManualAssignPackage'])->name('subscription.manual_assign_package');
        Route::get('subscription/flutterwave/{sid}/{tx_ref}', [PaymentController::class, 'subscriptionFlutterwave'])->name('subscription.flutterwave');

        Route::post('/subscription-pay-with-paystack', [PaymentController::class, 'subscriptionPayWithPaystack'])->name('subscription.pay.with.paystack')->middleware(['auth', 'XSS']);
        Route::get('/subscription/paystack/{pay_id}/{plan_id}', [PaymentController::class, 'getsubscriptionsPaymentStatus'])->name('subscription.paystack');
    }
);

//-------------------------------Notification-------------------------------------------
Route::resource('notification', NotificationController::class)->middleware(
    [
        'auth',
        'XSS',

    ]
);

Route::get('email-verification/{token}', [VerifyEmailController::class, 'verifyEmail'])->name('email-verification')->middleware(
    [
        'XSS',
    ]
);

//-------------------------------FAQ-------------------------------------------
Route::resource('FAQ', FAQController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Home Page-------------------------------------------
Route::resource('homepage', HomePageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------FAQ-------------------------------------------
Route::resource('pages', PageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Auth page-------------------------------------------
Route::resource('authPage', AuthPageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


Route::get('page/{slug}', [PageController::class, 'page'])->name('page');
//-------------------------------FAQ-------------------------------------------


//-------------------------------Branch-------------------------------------------
Route::resource('branch', BranchController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------Loan Type-------------------------------------------
Route::resource('loan-type', LoanTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------Document Type-------------------------------------------
Route::resource('document-type', DocumentTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------Customer-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('customer/{id}/document/create', [CustomerController::class, 'documentCreate'])->name('customer.document.create');
        Route::post('customer/{id}/document/store', [CustomerController::class, 'documentStore'])->name('customer.document.store');
        Route::delete('customer/{id}/document/{did}/destroy', [CustomerController::class, 'documentDestroy'])->name('customer.document.destroy');
        Route::resource('customer', CustomerController::class);
    }
);

//-------------------------------Loan-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        // Restore proper customer/admin separation for main loan route
        Route::get('loan', [LoanController::class, 'index'])->name('loan.index');
        
        // Keep other loan management routes for admins
        Route::get('loan/create', [LoanController::class, 'create'])->name('loan.create');
        Route::post('loan', [LoanController::class, 'store'])->name('loan.store');
        Route::get('loan/{loan}', [LoanController::class, 'show'])->name('loan.show');
        Route::get('loan/{loan}/edit', [LoanController::class, 'edit'])->name('loan.edit');
        Route::put('loan/{loan}', [LoanController::class, 'update'])->name('loan.update');
        Route::delete('loan/{loan}', [LoanController::class, 'destroy'])->name('loan.destroy');
        
        Route::get('loan/apply/{loanTypeId}', [LoanController::class, 'apply'])->name('loan.apply');
        Route::get('loan/{id}/approve', [LoanController::class, 'approve'])->name('loan.approve');
        Route::put('loan/{id}/approve', [LoanController::class, 'updateApproval'])->name('loan.updateApproval');
        Route::get('loan/{id}/reminder', [LoanController::class, 'paymentRemind'])->name('payment.reminder');
        Route::post('loan/{id}/reminder', [LoanController::class, 'paymentRemindData'])->name('payment.sendEmail');
        
        // Loan Disbursement Routes
        Route::get('disbursement', [App\Http\Controllers\DisbursementController::class, 'index'])->name('disbursement.index');
        Route::get('disbursement/{loan}', [App\Http\Controllers\DisbursementController::class, 'show'])->name('disbursement.show');
        Route::post('disbursement/{loan}/pay-file-charges', [App\Http\Controllers\DisbursementController::class, 'payFileCharges'])->name('disbursement.pay-file-charges');
        Route::post('disbursement/{loan}/waive-file-charges', [App\Http\Controllers\DisbursementController::class, 'waiveFileCharges'])->name('disbursement.waive-file-charges');
        Route::post('disbursement/{loan}/disburse-loan', [App\Http\Controllers\DisbursementController::class, 'disburseLoan'])->name('disbursement.disburse-loan');
    }
);

// Modern Loan Application Routes
Route::get('loans/modern', [LoanApplicationController::class, 'index'])->name('loans.modern'); // Keep modern interface accessible
Route::get('loan/application', [LoanApplicationController::class, 'application'])->name('loan.application');
Route::get('loan/wizard/{loanTypeId}', [LoanApplicationController::class, 'wizard'])->name('loan.wizard');
Route::post('loan/calculate-emi', [LoanApplicationController::class, 'calculateEMI'])->name('loan.calculate-emi');
Route::post('loan/submit-application', [LoanApplicationController::class, 'submitApplication'])->name('loan.submit-application');
Route::post('loan/submit-legacy-application', [LoanApplicationController::class, 'submitLegacyApplication'])->middleware('auth')->name('loan.submit-legacy-application');

//-------------------------------Loan Cycles-------------------------------------------
Route::resource('loan-cycle', LoanCycleController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Account Type-------------------------------------------
Route::resource('account-type', AccountTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Account-------------------------------------------
Route::resource('account', AccountController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Transaction-------------------------------------------
Route::resource('transaction', TransactionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
Route::post('get-account', [TransactionController::class, 'getAccount'])->name('customer.account');

//-------------------------------Expense-------------------------------------------
Route::resource('expense', ExpenseController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Repayments-------------------------------------------
Route::resource('repayment', RepaymentController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::get('repayment/schedule-payment/{id}', [RepaymentController::class, 'schedulesPayment'])->name('schedule.payment')->middleware(['auth', 'XSS']);
Route::get('repayment/schedule-payment-ap/{id}', [RepaymentController::class, 'schedulesPaymentAP'])->name('schedule.payment.ap')->middleware(['auth', 'XSS']);
Route::get('repayment/schedule-payment-status/{id}/{status}', [RepaymentController::class, 'schedulesPaymentStatus'])->name('schedule.payment.status')->middleware(['auth', 'XSS']);
Route::get('schedule/schedule-filter', [RepaymentController::class, 'loanFilter'])->name('schedule.filetr')->middleware(['auth', 'XSS']);


Route::post('invoice/{id}/banktransfer/payment', [RepaymentController::class, 'banktransferPayment'])->name('invoice.banktransfer.payment')->middleware(['auth', 'XSS']);
Route::post('invoice/{id}/stripe/payment', [RepaymentController::class, 'stripePayment'])->name('invoice.stripe.payment')->middleware(['auth', 'XSS']);
Route::post('invoice/{id}/paypal', [RepaymentController::class, 'invoicePaypal'])->name('invoice.paypal')->middleware(['auth', 'XSS']);
Route::get('invoice/{id}/paypal/{status}', [RepaymentController::class, 'invoicePaypalStatus'])->name('invoice.paypal.status')->middleware(['auth', 'XSS']);
Route::get('invoice/flutterwave/{id}/{tx_ref}', [RepaymentController::class, 'invoiceFlutterwave'])->name('invoice.flutterwave')->middleware(['auth', 'XSS']);
Route::post('invoice/{id}/paystack/payment', [RepaymentController::class, 'invoicePaystack'])->name('invoice.paystack.payment')->middleware(['auth', 'XSS']);
Route::get('/invoice/paystack/{pay_id}/{i_id}', [RepaymentController::class, 'invoicePaystackStatus'])->name('invoice.paystack')->middleware(['auth', 'XSS']);



Route::post('get-loan-installment', [RepaymentController::class, 'getLoanInstallment'])->name('loan.installment')->middleware(['auth', 'XSS']);
Route::get('repayment-schedules', [RepaymentController::class, 'schedules'])->name('repayment.schedules')->middleware(['auth', 'XSS']);
Route::delete('repayment-schedules-destroy/{id}', [RepaymentController::class, 'scheduleDestroy'])->name('repayment.schedules.destroy')->middleware(['auth', 'XSS']);

// Redirect for common URL mistakes
Route::get('repayment-schedule', function() {
    return redirect()->route('repayment.schedules');
})->middleware(['auth', 'XSS']);

// Redirect upcoming-installments to new Pay EMI page
Route::get('upcoming-installments', function() {
    return redirect('/pwa/pay-emi');
})->middleware(['auth', 'XSS']);


Route::impersonate();
