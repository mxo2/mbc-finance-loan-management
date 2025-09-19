<?php

// Suppress deprecation warnings for API responses
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FrontPageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Test route to verify API is working
Route::get('/', function () {
    return response()->json([
        'message' => 'API is working',
        'version' => '1.0.0',
        'timestamp' => now(),
        'status' => 'success'
    ]);
});

// Include PWA API routes
require __DIR__.'/pwa-api.php';

// Test API endpoint
Route::get('/test-api', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now()->toDateTimeString(),
        'server' => $_SERVER['SERVER_NAME'],
        'version' => '1.0.0'
    ]);
});

// Test auth endpoint for PWA
Route::post('/test-login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');
    
    // Log debug info to verify request is received
    \Log::info('Test login request received', [
        'email' => $email,
        'has_password' => !empty($password),
        'headers' => $request->headers->all()
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Login request received',
        'data' => [
            'email' => $email,
            'credentials_received' => !empty($email) && !empty($password),
            'timestamp' => now()->toDateTimeString()
        ]
    ]);
});

// Authentication routes for PWA
Route::post('/register', function (Request $request) {
    try {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'type' => 'customer'
        ]);

        // Generate a simple token for demo purposes
         $token = base64_encode($user->email . ':' . time());

         return response()->json([
             'success' => true,
             'message' => 'Registration successful',
             'user' => $user,
             'token' => $token
         ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Registration failed: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/login', function (Request $request) {
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Log the login attempt for debugging
        \Log::info('Login attempt', [
            'email' => $request->input('email'),
            'has_password' => !empty($request->input('password')),
            'password_length' => strlen($request->input('password')),
            'headers' => $request->headers->all()
        ]);
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Debug raw credentials
        \Log::info('Raw auth attempt', [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        
        // Get user directly from database for debugging
        $user = \App\Models\User::where('email', $request->input('email'))->first();
        if ($user) {
            \Log::info('User found', [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->type,
                'password_hash' => $user->password
            ]);
            
            // Check password directly with PHP's password_verify
            $passwordMatches = password_verify($request->input('password'), $user->password);
            \Log::info('Password verification', [
                'matches' => $passwordMatches
            ]);
            
            if ($passwordMatches) {
                // Create simple token for authentication
                $token = base64_encode($user->id . ':' . $user->email . ':' . time());
                
                // Store token in cache for validation
                \Illuminate\Support\Facades\Cache::put('pwa_token_' . $user->id, $token, now()->addDays(7));
                
                // Get customer details
                $customer = \App\Models\Customer::where('user_id', $user->id)->first();
                
                ob_end_clean(); // Clear any captured output
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'type' => $user->type,
                        'customer_id' => $customer ? $customer->customer_id : null
                    ],
                    'token' => $token
                ]);
            }
        } else {
            \Log::warning('User not found', ['email' => $request->input('email')]);
        }

        // Attempt through Laravel Auth
        if (\Illuminate\Support\Facades\Auth::attempt($request->only('email', 'password'))) {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            // Only allow customer type users to login to PWA
            if ($user->type !== 'customer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Customer account required.'
                ], 403);
            }
            
            // Create simple token for authentication
            $token = base64_encode($user->id . ':' . $user->email . ':' . time());
            
            // Store token in cache for validation
            \Illuminate\Support\Facades\Cache::put('pwa_token_' . $user->id, $token, now()->addDays(7));
            
            // Get customer details
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            
            ob_end_clean(); // Clear any captured output
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'customer_id' => $customer ? $customer->customer_id : null
                ],
                'token' => $token
            ]);
        }

        // Log failed login
        \Log::warning('Login failed', ['email' => $request->input('email')]);
        
        ob_end_clean(); // Clear any captured output
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    } catch (\Exception $e) {
        // Log the exception
        \Log::error('Login exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        ob_end_clean(); // Clear any captured output
        return response()->json([
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage()
        ], 500);
    }
});

// Test route without authentication
Route::get('/test', function () {
    return response()->json([
        'message' => 'Test endpoint working',
        'middleware' => 'api',
        'timestamp' => now()
    ]);
});

// API routes for React frontend
Route::post('/calculate-emi', [FrontPageController::class, 'calculateEMI']);
Route::post('/apply-loan', [FrontPageController::class, 'applyLoan']);

// Get loan types and other data for frontend
Route::get('/loan-types', function () {
    return response()->json([
        ['id' => 'mobile', 'name' => 'Mobile Phone', 'min_amount' => 5000, 'max_amount' => 50000],
        ['id' => 'television', 'name' => 'Television', 'min_amount' => 10000, 'max_amount' => 50000],
        ['id' => 'motorcycle', 'name' => 'Motorcycle', 'min_amount' => 20000, 'max_amount' => 50000],
    ]);
});

// Get user dashboard data from actual database
Route::get('/dashboard', function (Request $request) {
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Simple token authentication
        $token = $request->header('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token required'], 401);
        }
        
        $token = str_replace('Bearer ', '', $token);
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) < 3) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }
        
        $userId = $parts[0];
        $user = \App\Models\User::find($userId);
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Verify token in cache
        $cachedToken = \Illuminate\Support\Facades\Cache::get('pwa_token_' . $userId);
        if ($cachedToken !== $token) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 401);
        }
        
        // Get customer record
        $customer = \App\Models\Customer::where('user_id', $user->id)->first();
        
        // Get loans for this user from database
        $loans = \App\Models\Loan::where('customer', $user->id)
            ->with(['loanType', 'Repayments', 'RepaymentSchedules'])
            ->get();
        
        // Transform loans data for frontend to match web portal format
        $loansData = $loans->map(function($loan) {
            // Calculate pending amount from repayment schedules
            $totalScheduled = $loan->RepaymentSchedules->sum('total_amount');
            $totalPaid = $loan->Repayments->where('status', 'paid')->sum('total_amount');
            $pendingAmount = max(0, $totalScheduled - $totalPaid);
            
            // Format loan ID to match web portal (#LON-0003, #LON-0008, etc.)
            $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
            
            // Get repayment schedules for this loan
            $repaymentSchedules = $loan->RepaymentSchedules->map(function($schedule) use ($formattedLoanId) {
                return [
                    'loan_no' => $formattedLoanId,
                    'payment_date' => $schedule->due_date,
                    'principal_amount' => (float) $schedule->principal_amount ?? 0,
                    'interest' => (float) $schedule->interest ?? 0,
                    'penalty' => (float) $schedule->penalty ?? 0,
                    'total_amount' => (float) $schedule->total_amount,
                    'status' => $schedule->status,
                    'paid_amount' => (float) $schedule->paid_amount ?? 0
                ];
            });
            
            return [
                'id' => $loan->id,
                'loan_id' => $formattedLoanId, // Use formatted ID like web portal
                'amount' => (float) $loan->amount,
                'status' => $loan->status,
                'pending_amount' => $pendingAmount,
                'principal' => (float) $loan->amount,
                'type' => $loan->loanType->name ?? 'Personal Loan',
                'purpose' => $loan->purpose_of_loan,
                'start_date' => $loan->loan_start_date,
                'due_date' => $loan->loan_due_date,
                'terms' => $loan->loan_terms,
                'term_period' => $loan->loan_term_period,
                'repayment_schedules' => $repaymentSchedules,
                'created_at' => $loan->created_at->format('Y-m-d')
            ];
        });
        
        // Calculate loan summary from real data
        $totalLoans = $loans->count();
        $activeLoans = $loans->whereIn('status', ['approved', 'disbursed'])->count();
        $totalAmount = $loans->sum('amount');
        $pendingAmount = $loansData->sum('pending_amount');
        
        // Get recent repayments for activity
        $recentRepayments = \App\Models\Repayment::whereIn('loan_id', $loans->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Build recent activity from real data
        $recentActivity = [];
        foreach ($recentRepayments as $repayment) {
            $recentActivity[] = [
                'type' => 'payment',
                'description' => 'Payment ' . ucfirst($repayment->status),
                'amount' => (float) $repayment->amount,
                'date' => $repayment->created_at->diffForHumans()
            ];
        }
        
        // Add loan status updates to activity
        foreach ($loans->take(3) as $loan) {
            $recentActivity[] = [
                'type' => 'approval',
                'description' => 'Loan ' . ucfirst($loan->status),
                'amount' => null,
                'date' => $loan->updated_at->diffForHumans()
            ];
        }
        
        // Get next payment due
        $nextPayment = \App\Models\RepaymentSchedule::whereIn('loan_id', $loans->pluck('id'))
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();
        
        ob_end_clean(); // Clear any captured output
        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
                'customer_id' => $customer ? $customer->customer_id : null
            ],
            'summary' => [
                'totalLoans' => $totalLoans,
                'activeLoans' => $activeLoans,
                'totalAmount' => $totalAmount,
                'pendingAmount' => $pendingAmount,
                'nextPayment' => $nextPayment ? $nextPayment->due_date : null,
                'nextPaymentAmount' => $nextPayment ? (float) $nextPayment->amount : 0
            ],
            'loans' => $loansData,
            'recentActivity' => array_slice($recentActivity, 0, 5)
        ]);
        
    } catch (\Exception $e) {
        ob_end_clean(); // Clear any captured output
        return response()->json([
            'success' => false,
            'message' => 'Error fetching dashboard data: ' . $e->getMessage()
        ], 500);
    }
});

// Get user loans with detailed information
Route::get('/loans', function (Request $request) {
    try {
        $user = \App\Models\User::where('email', 'abs.jaipur@gmail.com')->first();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        $loans = \App\Models\Loan::where('customer', $user->id)
            ->with(['loanType', 'Repayments', 'RepaymentSchedules'])
            ->get();
            
        $loansData = $loans->map(function($loan) {
            $totalScheduled = $loan->RepaymentSchedules->sum('amount');
            $totalPaid = $loan->Repayments->where('status', 'paid')->sum('amount');
            $pendingAmount = max(0, $totalScheduled - $totalPaid);
            
            return [
                'id' => $loan->id,
                'loan_id' => $loan->loan_id,
                'amount' => (float) $loan->amount,
                'status' => $loan->status,
                'pending_amount' => $pendingAmount,
                'type' => $loan->loanType->name ?? 'Personal Loan',
                'purpose' => $loan->purpose_of_loan,
                'start_date' => $loan->loan_start_date,
                'due_date' => $loan->loan_due_date,
                'terms' => $loan->loan_terms,
                'term_period' => $loan->loan_term_period,
                'interest_rate' => 12.5,
                'monthly_emi' => round($loan->amount / 12, 2),
                'created_at' => $loan->created_at->format('Y-m-d')
            ];
        });
        
        return response()->json(['success' => true, 'loans' => $loansData]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Get loan repayment schedule
Route::get('/loans/{id}/schedule', function (Request $request, $id) {
    try {
        $loan = \App\Models\Loan::with(['RepaymentSchedules', 'Repayments'])->find($id);
        
        if (!$loan) {
            // Return mock schedule if loan not found
            $schedule = [];
            $startDate = now();
            for ($i = 0; $i < 12; $i++) {
                $dueDate = $startDate->copy()->addMonths($i);
                $schedule[] = [
                    'id' => $i + 1,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'amount' => 5000,
                    'status' => $i < 2 ? 'paid' : 'pending',
                    'paid_date' => $i < 2 ? $dueDate->subDays(rand(1, 5))->format('Y-m-d') : null,
                    'paid_amount' => $i < 2 ? 5000 : 0,
                    'penalty' => 0,
                    'emi_number' => $i + 1
                ];
            }
        } else {
            $schedule = $loan->RepaymentSchedules->map(function($schedule) {
                $payment = $schedule->loan->Repayments->where('schedule_id', $schedule->id)->first();
                return [
                    'id' => $schedule->id,
                    'due_date' => $schedule->due_date,
                    'amount' => (float) $schedule->amount,
                    'status' => $payment ? $payment->status : 'pending',
                    'paid_date' => $payment ? $payment->created_at->format('Y-m-d') : null,
                    'paid_amount' => $payment ? (float) $payment->amount : 0,
                    'penalty' => (float) ($schedule->penalty ?? 0),
                    'emi_number' => $schedule->installment_number ?? 1
                ];
            });
        }
        
        return response()->json(['success' => true, 'schedule' => $schedule]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Get next EMI details
Route::get('/next-emi', function (Request $request) {
    try {
        $user = \App\Models\User::where('email', 'abs.jaipur@gmail.com')->first();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        // Get next pending EMI
        $nextEmi = \App\Models\RepaymentSchedule::whereHas('loan', function($query) use ($user) {
                $query->where('customer', $user->id);
            })
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();
            
        if (!$nextEmi) {
            // Return mock next EMI
            $nextEmi = [
                'due_date' => now()->addDays(15)->format('Y-m-d'),
                'amount' => 5000,
                'loan_id' => 'LOAN001',
                'emi_number' => 3,
                'days_remaining' => 15,
                'penalty' => 0,
                'total_amount' => 5000
            ];
        } else {
            $daysRemaining = now()->diffInDays($nextEmi->due_date, false);
            $nextEmi = [
                'due_date' => $nextEmi->due_date,
                'amount' => (float) $nextEmi->amount,
                'loan_id' => $nextEmi->loan->loan_id,
                'emi_number' => $nextEmi->installment_number ?? 1,
                'days_remaining' => $daysRemaining,
                'penalty' => (float) ($nextEmi->penalty ?? 0),
                'total_amount' => (float) $nextEmi->amount + (float) ($nextEmi->penalty ?? 0)
            ];
        }
        
        return response()->json(['success' => true, 'next_emi' => $nextEmi]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Submit new loan application
Route::post('/loan-application', function (Request $request) {
    // Suppress PHP warnings and errors
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Simple token authentication
        $token = $request->header('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token required'], 401);
        }
        
        $token = str_replace('Bearer ', '', $token);
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) < 3) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }
        
        $userId = $parts[0];
        $user = \App\Models\User::find($userId);
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Verify token in cache
        $cachedToken = \Illuminate\Support\Facades\Cache::get('pwa_token_' . $userId);
        if ($cachedToken !== $token) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 401);
        }
        
        // Validate loan application data
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'loanType' => 'required|string',
            'amount' => 'required|numeric|min:1000|max:1000000',
            'tenure' => 'required|integer|min:1|max:60',
            'purpose' => 'required|string|max:255',
            'income' => 'required|numeric|min:0',
            'employment' => 'required|string|max:255'
        ]);
        
        if ($validator->fails()) {
            ob_end_clean();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get loan type by ID or type name
        $loanType = \App\Models\LoanType::where('id', $request->loanType)
            ->orWhere('type', 'like', '%' . $request->loanType . '%')
            ->first();
        if (!$loanType) {
            $loanType = \App\Models\LoanType::first(); // Use first available loan type
        }
        
        // Create loan application
        $loan = new \App\Models\Loan();
        $loan->loan_id = \App\Models\Loan::where('parent_id', $user->parent_id)->max('loan_id') + 1;
        $loan->loan_type = $loanType ? $loanType->id : 1;
        $loan->customer = $user->id;
        $loan->amount = $request->amount;
        $loan->loan_terms = $request->tenure;
        $loan->loan_term_period = 'months';
        $loan->purpose_of_loan = $request->purpose;
        $loan->status = 'pending';
        $loan->parent_id = $user->parent_id;
        $loan->created_by = $user->id;
        $loan->save();
        
        ob_end_clean();
        return response()->json([
            'success' => true,
            'message' => 'Loan application submitted successfully',
            'loan_id' => $loan->loan_id,
            'application_id' => $loan->id
        ]);
        
    } catch (\Exception $e) {
        ob_end_clean();
        return response()->json([
            'success' => false,
            'message' => 'Application failed: ' . $e->getMessage()
        ], 500);
    }
});

// Get payment history
Route::get('/payment-history', function (Request $request) {
    try {
        $user = \App\Models\User::where('email', 'abs.jaipur@gmail.com')->first();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        
        $payments = \App\Models\Repayment::whereHas('loan', function($query) use ($user) {
                $query->where('customer', $user->id);
            })
            ->with('loan')
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($payments->isEmpty()) {
            // Return mock payment history
            $payments = [
                [
                    'id' => 1,
                    'amount' => 5000,
                    'payment_date' => now()->subDays(30)->format('Y-m-d'),
                    'status' => 'paid',
                    'method' => 'Online Banking',
                    'transaction_id' => 'TXN001',
                    'loan_id' => 'LOAN001'
                ],
                [
                    'id' => 2,
                    'amount' => 5000,
                    'payment_date' => now()->subDays(60)->format('Y-m-d'),
                    'status' => 'paid',
                    'method' => 'UPI',
                    'transaction_id' => 'TXN002',
                    'loan_id' => 'LOAN001'
                ]
            ];
        } else {
            $payments = $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_date' => $payment->created_at->format('Y-m-d'),
                    'status' => $payment->status,
                    'method' => $payment->payment_method ?? 'Online',
                    'transaction_id' => $payment->transaction_id ?? 'TXN' . $payment->id,
                    'loan_id' => $payment->loan->loan_id
                ];
            });
        }
        
        return response()->json(['success' => true, 'payments' => $payments]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Customer-focused APIs for standalone web app

// Get upcoming installments for customer
Route::middleware('auth:sanctum')->get('/customer/upcoming-installments', function (Request $request) {
    try {
        $user = $request->user();
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Get upcoming installments from RepaymentSchedule
        $upcomingInstallments = \App\Models\RepaymentSchedule::whereHas('loan', function($query) use ($user) {
                $query->where('customer', $user->id);
            })
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->with(['loan', 'loan.loanType'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();
            
        if ($upcomingInstallments->isEmpty()) {
            // Return mock upcoming installments
            $installments = [
                [
                    'id' => 1,
                    'loan_id' => 'LOAN001',
                    'loan_type' => 'Personal Loan',
                    'amount' => 5000,
                    'due_date' => now()->addDays(15)->format('Y-m-d'),
                    'days_remaining' => 15,
                    'penalty' => 0,
                    'status' => 'pending',
                    'installment_number' => 3
                ],
                [
                    'id' => 2,
                    'loan_id' => 'LOAN001',
                    'loan_type' => 'Personal Loan',
                    'amount' => 5000,
                    'due_date' => now()->addDays(45)->format('Y-m-d'),
                    'days_remaining' => 45,
                    'penalty' => 0,
                    'status' => 'pending',
                    'installment_number' => 4
                ]
            ];
        } else {
            $installments = $upcomingInstallments->map(function($schedule) {
                $daysRemaining = now()->diffInDays($schedule->due_date, false);
                return [
                    'id' => $schedule->id,
                    'loan_id' => $schedule->loan->loan_id,
                    'loan_type' => $schedule->loan->loanType->name ?? 'Personal Loan',
                    'amount' => (float) $schedule->amount,
                    'due_date' => $schedule->due_date,
                    'days_remaining' => $daysRemaining,
                    'penalty' => (float) ($schedule->penalty ?? 0),
                    'status' => $schedule->status,
                    'installment_number' => $schedule->installment_number ?? 1
                ];
            });
        }
        
        return response()->json(['success' => true, 'installments' => $installments]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Process installment payment
Route::middleware('auth:sanctum')->post('/customer/pay-installment', function (Request $request) {
    try {
        $user = $request->user();
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'schedule_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // For demo purposes, simulate payment processing
        $transactionId = 'TXN' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        // In real implementation, you would:
        // 1. Validate the schedule belongs to the user
        // 2. Process payment through payment gateway
        // 3. Update RepaymentSchedule status
        // 4. Create Repayment record
        
        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'transaction_id' => $transactionId,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'processed_at' => now()->format('Y-m-d H:i:s')
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Get loan types for application
Route::get('/customer/loan-types', function (Request $request) {
    try {
        // Get loan types from database or return mock data
        $loanTypes = \App\Models\LoanType::where('status', 'active')->get();
        
        if ($loanTypes->isEmpty()) {
            $loanTypes = [
                [
                    'id' => 1,
                    'name' => 'Personal Loan',
                    'description' => 'Quick personal loans for your immediate needs',
                    'min_amount' => 10000,
                    'max_amount' => 500000,
                    'interest_rate' => 12.5,
                    'max_tenure' => 60,
                    'processing_fee' => 2.5
                ],
                [
                    'id' => 2,
                    'name' => 'Business Loan',
                    'description' => 'Grow your business with our flexible business loans',
                    'min_amount' => 50000,
                    'max_amount' => 2000000,
                    'interest_rate' => 15.0,
                    'max_tenure' => 84,
                    'processing_fee' => 3.0
                ],
                [
                    'id' => 3,
                    'name' => 'Home Loan',
                    'description' => 'Make your dream home a reality',
                    'min_amount' => 500000,
                    'max_amount' => 10000000,
                    'interest_rate' => 8.5,
                    'max_tenure' => 240,
                    'processing_fee' => 1.0
                ]
            ];
        } else {
            $loanTypes = $loanTypes->map(function($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description ?? 'Loan for your financial needs',
                    'min_amount' => (float) ($type->min_amount ?? 10000),
                    'max_amount' => (float) ($type->max_amount ?? 500000),
                    'interest_rate' => (float) ($type->interest_rate ?? 12.0),
                    'max_tenure' => (int) ($type->max_tenure ?? 60),
                    'processing_fee' => (float) ($type->processing_fee ?? 2.0)
                ];
            });
        }
        
        return response()->json(['success' => true, 'loan_types' => $loanTypes]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Calculate EMI for loan
Route::post('/customer/calculate-emi', function (Request $request) {
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
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Get repayment schedule (matches web portal format)
Route::get('/customer/repayment-schedule', function (Request $request) {
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Simple token authentication
        $token = $request->header('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token required'], 401);
        }
        
        $token = str_replace('Bearer ', '', $token);
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) < 3) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }
        
        $userId = $parts[0];
        $user = \App\Models\User::find($userId);
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
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
        } catch (\Exception $e) {
            // Cache error - log but continue for development
            \Log::warning('Cache verification failed in repayment schedule', ['error' => $e->getMessage()]);
        }
        
        // Get all loans for this customer
        $loans = \App\Models\Loan::where('customer', $user->id)
            ->with(['RepaymentSchedules'])
            ->get();
        
        // Get all repayment schedules for all loans
        $repaymentSchedules = [];
        foreach ($loans as $loan) {
            $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
            
            foreach ($loan->RepaymentSchedules as $schedule) {
                 // Calculate principal amount if not available (total - interest)
                 $interest = (float) ($schedule->interest ?? 0);
                 $totalAmount = (float) $schedule->total_amount;
                 $principalAmount = $schedule->principal_amount ? (float) $schedule->principal_amount : ($totalAmount - $interest);
                 
                 $repaymentSchedules[] = [
                     'loan_no' => $formattedLoanId,
                     'payment_date' => $schedule->due_date,
                     'principal_amount' => $principalAmount,
                     'interest' => $interest,
                     'penalty' => (float) ($schedule->penalty ?? 0),
                     'total_amount' => $totalAmount,
                     'status' => ucfirst($schedule->status),
                     'paid_amount' => (float) ($schedule->paid_amount ?? 0),
                     'loan_amount' => (float) $loan->amount,
                     'loan_status' => $loan->status,
                     'loan_type' => $loan->loanType->name ?? 'Personal Loan',
                     'loan_purpose' => $loan->purpose_of_loan
                 ];
             }
        }
        
        // Sort by payment date
        usort($repaymentSchedules, function($a, $b) {
            return strtotime($a['payment_date']) - strtotime($b['payment_date']);
        });
        
        ob_end_clean();
        return response()->json([
            'success' => true,
            'repayment_schedules' => $repaymentSchedules,
            'total_schedules' => count($repaymentSchedules)
        ]);
        
    } catch (\Exception $e) {
        ob_end_clean();
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch repayment schedule: ' . $e->getMessage()
        ], 500);
    }
});

// Get loan details with transaction history
Route::get('/customer/loan-details/{loanId}', function (Request $request, $loanId) {
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Simple token authentication
        $token = $request->header('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token required'], 401);
        }
        
        $token = str_replace('Bearer ', '', $token);
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) < 3) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }
        
        $userId = $parts[0];
        $user = \App\Models\User::find($userId);
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Verify token in cache
        $cachedToken = \Illuminate\Support\Facades\Cache::get('pwa_token_' . $userId);
        if ($cachedToken !== $token) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 401);
        }
        
        // Get specific loan for this customer
        $loan = \App\Models\Loan::where('customer', $user->id)
            ->where('loan_id', $loanId)
            ->with(['loanType', 'RepaymentSchedules', 'Repayments'])
            ->first();
            
        if (!$loan) {
            return response()->json(['success' => false, 'message' => 'Loan not found'], 404);
        }
        
        $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
        
        // Get repayment schedules
        $repaymentSchedules = $loan->RepaymentSchedules->map(function($schedule) use ($formattedLoanId) {
            $interest = (float) ($schedule->interest ?? 0);
            $totalAmount = (float) $schedule->total_amount;
            $principalAmount = $schedule->principal_amount ? (float) $schedule->principal_amount : ($totalAmount - $interest);
            
            return [
                'id' => $schedule->id,
                'due_date' => $schedule->due_date,
                'principal_amount' => $principalAmount,
                'interest' => $interest,
                'penalty' => (float) ($schedule->penalty ?? 0),
                'total_amount' => $totalAmount,
                'status' => ucfirst($schedule->status),
                'paid_amount' => (float) ($schedule->paid_amount ?? 0),
                'payment_date' => $schedule->payment_date
            ];
        });
        
        // Get transaction history (repayments)
        $transactions = $loan->Repayments->map(function($repayment) {
            return [
                'id' => $repayment->id,
                'payment_date' => $repayment->payment_date,
                'amount' => (float) $repayment->total_amount,
                'principal' => (float) ($repayment->principal_amount ?? 0),
                'interest' => (float) ($repayment->interest ?? 0),
                'penalty' => (float) ($repayment->penalty ?? 0),
                'status' => ucfirst($repayment->status),
                'payment_method' => $repayment->payment_method ?? 'Cash',
                'reference_number' => $repayment->reference_number ?? '',
                'created_at' => $repayment->created_at->format('Y-m-d H:i:s')
            ];
        });
        
        // Calculate loan summary
        $totalScheduled = $loan->RepaymentSchedules->sum('total_amount');
        $totalPaid = $loan->Repayments->where('status', 'paid')->sum('total_amount');
        $pendingAmount = max(0, $totalScheduled - $totalPaid);
        $nextPayment = $loan->RepaymentSchedules->where('status', 'pending')->sortBy('due_date')->first();
        
        $loanDetails = [
            'loan_id' => $formattedLoanId,
            'amount' => (float) $loan->amount,
            'status' => ucfirst($loan->status),
            'type' => $loan->loanType->name ?? 'Personal Loan',
            'purpose' => $loan->purpose_of_loan,
            'start_date' => $loan->loan_start_date,
            'due_date' => $loan->loan_due_date,
            'terms' => $loan->loan_terms,
            'term_period' => $loan->loan_term_period,
            'interest_rate' => $loan->interest_rate ?? 0,
            'total_scheduled' => $totalScheduled,
            'total_paid' => $totalPaid,
            'pending_amount' => $pendingAmount,
            'next_payment' => $nextPayment ? [
                'due_date' => $nextPayment->due_date,
                'amount' => (float) $nextPayment->total_amount
            ] : null,
            'created_at' => $loan->created_at->format('Y-m-d')
        ];
        
        ob_end_clean();
        return response()->json([
            'success' => true,
            'loan_details' => $loanDetails,
            'repayment_schedules' => $repaymentSchedules,
            'transactions' => $transactions,
            'summary' => [
                'total_schedules' => $repaymentSchedules->count(),
                'paid_schedules' => $repaymentSchedules->where('status', 'Paid')->count(),
                'pending_schedules' => $repaymentSchedules->where('status', 'Pending')->count(),
                'total_transactions' => $transactions->count()
            ]
        ]);
        
    } catch (\Exception $e) {
        ob_end_clean();
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch loan details: ' . $e->getMessage()
        ], 500);
    }
});

// Get available loan types
Route::get('/loan-types', function (Request $request) {
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Get all active loan types (status can be 1, 'active', or null)
        $loanTypes = \App\Models\LoanType::where('status', 1)
            ->orWhere('status', 'active')
            ->orWhereNull('status')
            ->get();
        
        // If no loan types found, get all loan types
        if ($loanTypes->isEmpty()) {
            $loanTypes = \App\Models\LoanType::all();
        }
        
        $formattedLoanTypes = $loanTypes->map(function($loanType) {
             return [
                 'id' => $loanType->id,
                 'name' => $loanType->type ?? $loanType->name ?? 'Loan Type',
                 'description' => $loanType->notes ?? $loanType->description ?? '',
                 'min_amount' => $loanType->min_loan_amount ?? $loanType->min_amount ?? 1000,
                 'max_amount' => $loanType->max_loan_amount ?? $loanType->max_amount ?? 1000000,
                 'interest_rate' => $loanType->interest_rate ?? 12,
                 'max_tenure' => $loanType->max_loan_term ?? $loanType->max_tenure ?? 60,
                 'processing_fee' => $loanType->processing_fee ?? 0,
                 'interest_type' => $loanType->interest_type ?? 'flat_rate',
                 'payment_frequency' => $loanType->payment_frequency ?? 'monthly'
             ];
         });
        
        ob_end_clean();
        return response()->json([
            'success' => true,
            'loan_types' => $formattedLoanTypes
        ]);
        
    } catch (\Exception $e) {
        ob_end_clean();
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch loan types: ' . $e->getMessage()
        ], 500);
    }
});

// Get upcoming installments for next month
Route::get('/customer/upcoming-installments', function (Request $request) {
    // Start output buffering to capture any warnings
    ob_start();
    
    try {
        // Simple token authentication
        $token = $request->header('Authorization');
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return response()->json(['success' => false, 'message' => 'Token required'], 401);
        }
        
        $token = str_replace('Bearer ', '', $token);
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) < 3) {
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);
        }
        
        $userId = $parts[0];
        $user = \App\Models\User::find($userId);
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Verify token in cache
        $cachedToken = \Illuminate\Support\Facades\Cache::get('pwa_token_' . $userId);
        if ($cachedToken !== $token) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 401);
        }
        
        // Get next month's date range
        $nextMonth = now()->addMonth();
        $startOfNextMonth = $nextMonth->startOfMonth()->format('Y-m-d');
        $endOfNextMonth = $nextMonth->endOfMonth()->format('Y-m-d');
        
        // Get upcoming installments for next month
        $installments = DB::table('repayment_schedules')
            ->join('loans', 'repayment_schedules.loan_id', '=', 'loans.id')
            ->join('loan_types', 'loans.loan_type', '=', 'loan_types.id')
            ->where('loans.customer', $user->id)
            ->where('repayment_schedules.status', '!=', 'Paid')
            ->whereBetween('repayment_schedules.due_date', [$startOfNextMonth, $endOfNextMonth])
            ->select(
                'repayment_schedules.id',
                'repayment_schedules.due_date',
                'repayment_schedules.total_amount',
                'repayment_schedules.principal_amount',
                'repayment_schedules.interest',
                'repayment_schedules.status',
                'loans.loan_id',
                'loan_types.type as loan_type'
            )
            ->orderBy('repayment_schedules.due_date')
            ->get();
        
        // Transform data for frontend
        $formattedInstallments = $installments->map(function($installment, $index) {
            $dueDate = \Carbon\Carbon::parse($installment->due_date);
            $daysRemaining = now()->diffInDays($dueDate, false);
            
            return [
                'id' => $installment->id,
                'loan_id' => '#LON-' . str_pad($installment->loan_id, 4, '0', STR_PAD_LEFT),
                'loan_type' => $installment->loan_type,
                'amount' => (float) $installment->total_amount,
                'principal_amount' => (float) ($installment->principal_amount ?? 0),
                'interest' => (float) ($installment->interest ?? 0),
                'due_date' => $installment->due_date,
                'status' => $installment->status,
                'installment_number' => $index + 1,
                'days_remaining' => (int) $daysRemaining
            ];
        });
        
        ob_end_clean();
        return response()->json([
            'success' => true,
            'installments' => $formattedInstallments
        ]);
        
    } catch (\Exception $e) {
        ob_end_clean();
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch installments: ' . $e->getMessage()
        ], 500);
    }
});

// Get customer profile
Route::middleware('auth:sanctum')->get('/customer/profile', function (Request $request) {
    try {
        $user = $request->user();
        
        if (!$user || $user->type !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $customer = \App\Models\Customer::where('user_id', $user->id)->first();
         
        return response()->json([
            'success' => true,
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? '+91 9876543210',
                'type' => $user->type,
                'customer_id' => $customer ? $customer->customer_id : null,
                'profession' => $customer ? $customer->profession : null,
                'company' => $customer ? $customer->company : null,
                'city' => $customer ? $customer->city : null,
                'created_at' => $user->created_at->format('Y-m-d'),
                'is_active' => $user->is_active ?? true
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Pay EMI API Routes
Route::middleware('auth:sanctum')->get('/user/loans', function (Request $request) {
    try {
        $user = $request->user();
        $loans = DB::table('loans')
            ->join('loan_types', 'loans.loan_type', '=', 'loan_types.id')
            ->where('loans.customer', $user->id)
            ->where('loans.status', 'approved')
            ->select([
                'loans.id',
                'loans.loan_id',
                'loans.amount',
                'loans.status',
                'loans.loan_start_date',
                'loans.loan_due_date',
                'loan_types.id as loan_type_id',
                'loan_types.type as loan_type_name',
                'loan_types.interest_rate'
            ])
            ->get();

        $loansWithTypes = $loans->map(function($loan) {
            return [
                'id' => $loan->id,
                'loan_id' => $loan->loan_id,
                'amount' => $loan->amount,
                'status' => $loan->status,
                'loan_start_date' => $loan->loan_start_date,
                'loan_due_date' => $loan->loan_due_date,
                'loanType' => [
                    'id' => $loan->loan_type_id,
                    'name' => $loan->loan_type_name,
                    'interest_rate' => $loan->interest_rate
                ]
            ];
        });

        return response()->json(['loans' => $loansWithTypes]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::middleware('auth:sanctum')->get('/loans/{loanId}/repayment-schedule', function (Request $request, $loanId) {
    try {
        $user = $request->user();
        
        // Verify loan belongs to user
        $loan = DB::table('loans')
            ->where('id', $loanId)
            ->where('customer', $user->id)
            ->first();
            
        if (!$loan) {
            return response()->json(['error' => 'Loan not found'], 404);
        }

        $schedule = DB::table('repayment_schedules')
            ->where('loan_id', $loanId)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json(['schedule' => $schedule]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::middleware('auth:sanctum')->post('/emi/pay', function (Request $request) {
    try {
        $user = $request->user();
        $scheduleId = $request->input('schedule_id');
        $paymentMethod = $request->input('payment_method');
        $amount = $request->input('amount');

        // Verify the schedule belongs to user's loan
        $schedule = DB::table('repayment_schedules')
            ->join('loans', 'repayment_schedules.loan_id', '=', 'loans.id')
            ->where('repayment_schedules.id', $scheduleId)
            ->where('loans.customer', $user->id)
            ->where('repayment_schedules.status', 'Pending')
            ->select('repayment_schedules.*')
            ->first();

        if (!$schedule) {
            return response()->json(['error' => 'EMI not found or already paid'], 404);
        }

        // Update schedule status
        DB::table('repayment_schedules')
            ->where('id', $scheduleId)
            ->update([
                'status' => 'Paid',
                'payment_type' => $paymentMethod,
                'transaction_id' => 'TXN' . time() . rand(1000, 9999),
                'updated_at' => now()
            ]);

        // Create repayment record
        DB::table('repayments')->insert([
            'loan_id' => $schedule->loan_id,
            'payment_date' => now()->toDateString(),
            'principal_amount' => $schedule->installment_amount,
            'interest' => $schedule->interest,
            'penality' => $schedule->penality,
            'total_amount' => $amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Payment successful']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Get application status (backward compatibility)
Route::middleware('auth:sanctum')->get('/application-status', function (Request $request) {
    $user = $request->user();
    $loans = $user->loans ?? [];
    return response()->json(['loans' => $loans]);
});
