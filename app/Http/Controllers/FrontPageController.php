<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanType;

class FrontPageController extends Controller
{
    /**
     * Display the standalone homepage without CMS integration.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Simple, clean homepage without CMS dependencies
        return view('front.homepage');
    }
    
    /**
     * Handle loan application from the front page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function applyLoan(Request $request)
    {
        // Get loan type to determine validation limits
        $loanTypeId = $request->input('loan_type');
        $loanType = null;
        
        if ($loanTypeId) {
            $loanType = \App\Models\LoanType::find($loanTypeId);
        }
        
        // Set dynamic validation rules based on loan type
        $minAmount = $loanType ? $loanType->min_loan_amount : 5000;
        $maxAmount = $loanType ? $loanType->max_loan_amount : 5000000; // Default to 50 lakh if no loan type
        
        // Validate basic application data with dynamic loan amount limits
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'loan_type' => 'required|exists:loan_types,id',
            'loan_amount' => "required|numeric|min:{$minAmount}|max:{$maxAmount}",
            'loan_purpose' => 'required|string|max:255',
            'tenure' => 'nullable|numeric',
            'income' => 'nullable|numeric',
            'employment' => 'nullable|string|max:255',
        ]);
        
        // Check if this is an API request (JSON)
        if ($request->expectsJson() || $request->is('api/*')) {
            // For API requests, handle custom token authentication
            $token = $request->header('Authorization');
            
            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to submit loan application. Please login first.',
                ], 401);
            }
            
            $token = str_replace('Bearer ', '', $token);
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);
            
            if (count($parts) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication token. Please login again.',
                ], 401);
            }
            
            $userId = $parts[0];
            $user = \App\Models\User::find($userId);
            
            if (!$user || $user->type !== 'customer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Customer account required.',
                ], 401);
            }
            
            // Verify token in cache (with fallback for cache issues)
            try {
                $cachedToken = \Illuminate\Support\Facades\Cache::get('pwa_token_' . $userId);
                if ($cachedToken && $cachedToken !== $token) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired authentication token. Please login again.',
                    ], 401);
                }
                // If no cached token found, we'll allow it (cache may have issues)
                // In production, you might want to be more strict
            } catch (\Exception $e) {
                // Cache error - log but continue (for development/testing)
                \Log::warning('Cache verification failed', ['error' => $e->getMessage()]);
            }
            
            // User is authenticated, proceed with loan application
            try {
                // Get customer record
                $customer = \App\Models\Customer::where('user_id', $user->id)->first();
                
                // Create loan application record
                $loan = new \App\Models\Loan();
                $loan->loan_id = \App\Models\Loan::where('parent_id', $user->parent_id ?? 0)->max('loan_id') + 1;
                $loan->loan_type = $validatedData['loan_type'];
                $loan->customer = $user->id;
                $loan->amount = $validatedData['loan_amount'];
                $loan->loan_terms = $validatedData['tenure'] ?? 12;
                $loan->loan_term_period = 'months';
                $loan->purpose_of_loan = $validatedData['loan_purpose'];
                $loan->status = 'pending';
                $loan->parent_id = $user->parent_id ?? 0;
                $loan->created_by = $user->id;
                
                // Set start date to today
                $loan->loan_start_date = now()->format('Y-m-d');
                
                // Store additional info in notes field if present
                $notes = [];
                if (isset($validatedData['income'])) {
                    $notes[] = 'Monthly Income: ₹' . number_format($validatedData['income']);
                }
                if (isset($validatedData['employment'])) {
                    $notes[] = 'Employment: ' . $validatedData['employment'];
                }
                if (!empty($notes)) {
                    $loan->notes = implode(' | ', $notes);
                }
                
                $loan->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Loan application submitted successfully. We will review your application and contact you soon.',
                    'data' => [
                        'loan_id' => $loan->loan_id,
                        'application_id' => $loan->id,
                        'amount' => $loan->amount,
                        'status' => $loan->status,
                        'customer_name' => $user->name,
                        'customer_email' => $user->email
                    ]
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Loan application creation failed', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit loan application. Please try again later.',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            // For web requests, use redirects
            if (Auth::check()) {
                // If user is logged in, redirect to loan creation
                return redirect()->route('loan.create')->with('application_data', $request->all());
            } else {
                // Store application data in session and redirect to register
                session(['loan_application' => $request->all()]);
                return redirect()->route('register')->with('message', 'Please register or login to complete your loan application');
            }
        }
    }
    
    /**
     * Calculate EMI for the loan calculator.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function calculateEMI(Request $request)
    {
        $loanAmount = $request->input('loan_amount', 25000);
        $tenure = $request->input('tenure', 6); // in months
        $interestRate = 5; // 5% per month
        
        // Simple EMI calculation
        $monthlyInterest = $interestRate / 100;
        $emi = ($loanAmount * $monthlyInterest * pow(1 + $monthlyInterest, $tenure)) / 
               (pow(1 + $monthlyInterest, $tenure) - 1);
        
        $totalAmount = $emi * $tenure;
        $totalInterest = $totalAmount - $loanAmount;
        
        return response()->json([
            'emi' => round($emi, 2),
            'total_amount' => round($totalAmount, 2),
            'total_interest' => round($totalInterest, 2),
            'loan_amount' => $loanAmount,
            'tenure' => $tenure,
            'interest_rate' => $interestRate
        ]);
    }
}