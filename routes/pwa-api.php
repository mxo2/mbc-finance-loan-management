<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PWAController;

/*
|--------------------------------------------------------------------------
| PWA API Routes
|--------------------------------------------------------------------------
|
| These routes are specifically designed for the PWA application.
| They handle authentication and customer-specific data.
|
*/

// PWA Authentication routes
Route::post('/pwa-login', [PWAController::class, 'login']);
Route::post('/pwa-logout', [PWAController::class, 'logout']);

// PWA Protected routes
Route::middleware(['throttle:60,1'])->group(function () {
    
    // User management
    Route::get('/pwa/user', [PWAController::class, 'getUser']);
    
    // Dashboard
    Route::get('/pwa/dashboard', [PWAController::class, 'getDashboard']);
    
    // Loans management  
    Route::get('/pwa/loans', [PWAController::class, 'getLoans']);
    Route::get('/pwa/loans/{loanId}', [PWAController::class, 'getLoanDetails']);
    
    // Repayment schedules
    Route::get('/pwa/repayment-schedule', [PWAController::class, 'getRepaymentSchedule']);
    Route::get('/pwa/repayment-schedules', [PWAController::class, 'getRepaymentSchedules']);
    Route::get('/pwa/loans/{loanId}/repayment-schedule', [PWAController::class, 'getLoanRepaymentSchedule']);
    
    // Transactions
    Route::get('/pwa/transactions', [PWAController::class, 'getTransactions']);
    
    // EMI Payments
    Route::post('/pwa/emi/pay', [PWAController::class, 'processEMIPayment']);
    
});

// Public routes (no authentication required)
Route::get('/pwa/loan-types', function () {
    try {
        $loanTypes = \App\Models\LoanType::where('status', 1)
            ->orWhere('status', 'active')
            ->orWhereNull('status')
            ->get();
        
        if ($loanTypes->isEmpty()) {
            $loanTypes = \App\Models\LoanType::all();
        }
        
        $formattedLoanTypes = $loanTypes->map(function($loanType) {
            return [
                'id' => $loanType->id,
                'name' => $loanType->type ?? 'Loan Type',
                'description' => $loanType->notes ?? '',
                'min_amount' => $loanType->min_loan_amount ?? 1000,
                'max_amount' => $loanType->max_loan_amount ?? 1000000,
                'interest_rate' => $loanType->interest_rate ?? 12,
                'max_tenure' => $loanType->max_loan_term ?? 60,
                'interest_type' => $loanType->interest_type ?? 'flat_rate',
                'penalties' => $loanType->penalties ?? 0
            ];
        });
        
        return response()->json([
            'success' => true,
            'loan_types' => $formattedLoanTypes
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch loan types: ' . $e->getMessage()
        ], 500);
    }
});

// EMI Calculator
Route::post('/pwa/calculate-emi', function (\Illuminate\Http\Request $request) {
    try {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000',
            'interest_rate' => 'required|numeric|min:1|max:50',
            'tenure' => 'required|integer|min:1|max:360'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $principal = $request->amount;
        $rate = $request->interest_rate / 100 / 12; // Monthly interest rate
        $tenure = $request->tenure;
        
        // EMI calculation formula: P * r * (1+r)^n / ((1+r)^n - 1)
        $emi = ($principal * $rate * pow(1 + $rate, $tenure)) / (pow(1 + $rate, $tenure) - 1);
        $totalAmount = $emi * $tenure;
        $totalInterest = $totalAmount - $principal;
        
        return response()->json([
            'success' => true,
            'emi' => round($emi, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2),
            'principal' => $principal,
            'interest_rate' => $request->interest_rate,
            'tenure' => $tenure
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Calculation failed: ' . $e->getMessage()
        ], 500);
    }
});