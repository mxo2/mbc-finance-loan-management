<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Loan;
use App\Models\Customer;
use App\Models\LoanType;
use App\Models\RepaymentSchedule;
use App\Models\Repayment;
use Carbon\Carbon;

class PWAController extends Controller
{
    /**
     * Login for PWA application
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find user by email
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Only allow customer type users to login to PWA
            if ($user->type !== 'customer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Customer account required.'
                ], 403);
            }

            // Create simple token for authentication
            $token = base64_encode($user->id . ':' . $user->email . ':' . time());
            
            // Store token in cache for validation (7 days)
            Cache::put('pwa_token_' . $user->id, $token, now()->addDays(7));
            
            // Get customer details
            $customer = Customer::where('user_id', $user->id)->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'customer_id' => $customer ? $customer->customer_id : null,
                    'kyc_status' => 'verified' // Default for demo
                ],
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function getUser(Request $request)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        $customer = Customer::where('user_id', $userData->id)->first();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $userData->id,
                'name' => $userData->name,
                'email' => $userData->email,
                'type' => $userData->type,
                'customer_id' => $customer ? $customer->customer_id : null,
                'kyc_status' => 'verified',
                'created_at' => $userData->created_at,
                'updated_at' => $userData->updated_at
            ]
        ]);
    }

    /**
     * Get dashboard data with real loan information
     */
    public function getDashboard(Request $request)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        try {
            // Get customer record
            $customer = Customer::where('user_id', $userData->id)->first();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer record not found'
                ], 404);
            }
            
            // Get loans for this user
            $loans = Loan::where('customer', $userData->id)
                ->get();
            
            // Calculate loan statistics
            $totalLoans = $loans->count();
            $activeLoans = $loans->whereIn('status', ['approved', 'disbursed'])->count();
            $totalAmount = $loans->sum('amount');
            
            // Get repayment schedules for all loans
            $repaymentSchedules = RepaymentSchedule::whereIn('loan_id', $loans->pluck('id'))->get();
            $repayments = Repayment::whereIn('loan_id', $loans->pluck('id'))->get();
            
            // Calculate pending amount from repayment schedules
            $totalScheduled = $repaymentSchedules->sum('total_amount');
            $totalPaid = $repayments->sum('total_amount');
            $pendingAmount = max(0, $totalScheduled - $totalPaid);
            
            // Get next EMI due
            $nextEmi = RepaymentSchedule::whereIn('loan_id', $loans->pluck('id'))
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();
            
            // Transform loans data - only include active/approved loans for dashboard
            $loansData = $loans->whereIn('status', ['approved', 'disbursed'])->map(function($loan) use ($repaymentSchedules, $repayments) {
                $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
                
                // Calculate pending amount for this loan
                $loanSchedules = $repaymentSchedules->where('loan_id', $loan->id);
                $loanRepayments = $repayments->where('loan_id', $loan->id);
                $totalScheduled = $loanSchedules->sum('total_amount');
                $totalPaid = $loanRepayments->sum('total_amount');
                $pendingAmount = max(0, $totalScheduled - $totalPaid);
                
                return [
                    'id' => $loan->id,
                    'loan_id' => $formattedLoanId,
                    'amount' => (float) $loan->amount,
                    'status' => ucfirst($loan->status),
                    'pending_amount' => $pendingAmount,
                    'type' => 'Personal Loan', // Default since relationship not available
                    'purpose' => $loan->purpose_of_loan,
                    'start_date' => $loan->loan_start_date,
                    'due_date' => $loan->loan_due_date,
                    'terms' => $loan->loan_terms,
                    'term_period' => $loan->loan_term_period,
                    'created_at' => $loan->created_at->format('Y-m-d')
                ];
            });
            
            // Get recent activity from repayments and loan updates
            $recentActivity = [];
            
            // Recent repayments
            $recentRepayments = Repayment::whereIn('loan_id', $loans->pluck('id'))
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
                
            foreach ($recentRepayments as $repayment) {
                $recentActivity[] = [
                    'type' => 'payment',
                    'title' => 'Payment Received',
                    'description' => 'EMI payment of ₹' . number_format($repayment->total_amount),
                    'icon' => '💰',
                    'date' => $repayment->created_at->diffForHumans(),
                    'amount' => (float) $repayment->total_amount
                ];
            }
            
            // Recent loan status updates
            foreach ($loans->take(2) as $loan) {
                $recentActivity[] = [
                    'type' => 'loan_status',
                    'title' => 'Loan ' . ucfirst($loan->status),
                    'description' => $loan->loanType->type ?? 'Personal Loan' . ' - ₹' . number_format($loan->amount),
                    'icon' => $loan->status === 'approved' ? '✅' : '📝',
                    'date' => $loan->updated_at->diffForHumans(),
                    'amount' => null
                ];
            }
            
            // Sort activity by date and limit to 5
            usort($recentActivity, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            $recentActivity = array_slice($recentActivity, 0, 5);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $userData->name,
                    'email' => $userData->email,
                    'type' => $userData->type,
                    'customer_id' => $customer ? $customer->customer_id : null,
                    'kyc_status' => 'verified'
                ],
                'summary' => [
                    'totalLoans' => $totalLoans,
                    'activeLoans' => $activeLoans,
                    'totalAmount' => $totalAmount,
                    'pendingAmount' => $pendingAmount,
                    'nextPayment' => $nextEmi ? $nextEmi->due_date : null,
                    'nextPaymentAmount' => $nextEmi ? (float) $nextEmi->total_amount : 0
                ],
                'loans' => $loansData,
                'recentActivity' => $recentActivity,
                'stats' => [
                    [
                        'label' => 'Active Loans',
                        'value' => (string) $activeLoans,
                        'icon' => '💰',
                        'color' => 'text-primary-600'
                    ],
                    [
                        'label' => 'Total Borrowed',
                        'value' => '₹' . number_format($totalAmount),
                        'icon' => '📊', 
                        'color' => 'text-success-600'
                    ],
                    [
                        'label' => 'KYC Status',
                        'value' => 'Verified',
                        'icon' => '✅',
                        'color' => 'text-success-600'
                    ]
                ],
                'nextEmi' => $nextEmi ? [
                    'due_date' => $nextEmi->due_date,
                    'amount' => (float) $nextEmi->total_amount,
                    'days_remaining' => Carbon::parse($nextEmi->due_date)->diffInDays(now()),
                    'loan_id' => '#LON-' . str_pad($loans->where('id', $nextEmi->loan_id)->first()->loan_id ?? $nextEmi->loan_id, 4, '0', STR_PAD_LEFT)
                ] : null
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard Error: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage(),
                'error' => $e->getLine() . ' in ' . basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get repayment schedule for customer
     */
    public function getRepaymentSchedule(Request $request)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        try {
            // Get all loans for this customer
            $loans = Loan::where('customer', $userData->id)
                ->with(['RepaymentSchedules', 'loanType'])
                ->get();
            
            // Get all repayment schedules
            $repaymentSchedules = [];
            foreach ($loans as $loan) {
                $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
                
                foreach ($loan->RepaymentSchedules as $schedule) {
                $interest = (float) ($schedule->interest ?? 0);
                $totalAmount = (float) $schedule->total_amount;
                $installmentAmount = (float) ($schedule->installment_amount ?? 0);
                $principalAmount = $installmentAmount > 0 ? $installmentAmount : ($totalAmount - $interest);                    $repaymentSchedules[] = [
                        'id' => $schedule->id,
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
                        'loan_type' => $loan->loanType->type ?? 'Personal Loan',
                        'loan_purpose' => $loan->purpose_of_loan,
                        'days_remaining' => Carbon::parse($schedule->due_date)->diffInDays(now(), false)
                    ];
                }
            }
            
            // Sort by payment date
            usort($repaymentSchedules, function($a, $b) {
                return strtotime($a['payment_date']) - strtotime($b['payment_date']);
            });
            
            return response()->json([
                'success' => true,
                'repayment_schedules' => $repaymentSchedules,
                'total_schedules' => count($repaymentSchedules),
                'summary' => [
                    'total_pending' => collect($repaymentSchedules)->where('status', 'Pending')->sum('total_amount'),
                    'total_paid' => collect($repaymentSchedules)->where('status', 'Paid')->sum('total_amount'),
                    'upcoming_this_month' => collect($repaymentSchedules)->filter(function($schedule) {
                        return Carbon::parse($schedule['payment_date'])->isCurrentMonth() && $schedule['status'] === 'Pending';
                    })->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch repayment schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get repayment schedule for a specific loan
     */
    public function getLoanRepaymentSchedule(Request $request, $loanId)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        try {
            // Get the specific loan for this customer
            $loan = Loan::where('id', $loanId)
                ->where('customer', $userData->id)
                ->with(['RepaymentSchedules', 'loanType'])
                ->first();
                
            if (!$loan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan not found'
                ], 404);
            }
            
            // Get repayment schedules for this specific loan
            $repaymentSchedules = [];
            $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
            
            foreach ($loan->RepaymentSchedules as $schedule) {
                $interest = (float) ($schedule->interest ?? 0);
                $totalAmount = (float) $schedule->total_amount;
                $installmentAmount = (float) ($schedule->installment_amount ?? 0);
                $principalAmount = $installmentAmount > 0 ? $installmentAmount : ($totalAmount - $interest);
                
                $repaymentSchedules[] = [
                    'id' => $schedule->id,
                    'loan_id' => $formattedLoanId,
                    'loan_database_id' => $loan->id,
                    'installment_number' => $schedule->installment_number,
                    'due_date' => $schedule->due_date,
                    'principal_amount' => $principalAmount,
                    'interest' => $interest,
                    'total_amount' => $totalAmount,
                    'status' => ucfirst($schedule->status ?? 'pending'),
                    'paid_date' => null, // Column doesn't exist in table
                    'late_fee' => (float) ($schedule->penality ?? 0),
                    'days_overdue' => $schedule->due_date ? (new \DateTime($schedule->due_date) < new \DateTime() ? (new \DateTime())->diff(new \DateTime($schedule->due_date))->days : 0) : 0,
                ];
            }
            
            return response()->json([
                'success' => true,
                'schedule' => $repaymentSchedules,
                'loan' => [
                    'id' => $loan->id,
                    'loan_id' => $formattedLoanId,
                    'amount' => (float) $loan->amount,
                    'type' => $loan->loanType->type ?? 'Unknown',
                    'status' => $loan->status
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch loan repayment schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get loans for customer
     */
    public function getLoans(Request $request)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        try {
            $loans = Loan::where('customer', $userData->id)
                ->with(['loanType', 'RepaymentSchedules', 'Repayments'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            $loansData = $loans->map(function($loan) {
                $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
                
                // Calculate amounts
                $totalScheduled = $loan->RepaymentSchedules->sum('total_amount');
                $totalPaid = $loan->Repayments->sum('total_amount');
                $pendingAmount = max(0, $totalScheduled - $totalPaid);
                
                // Get next EMI
                $nextEmi = $loan->RepaymentSchedules->where('status', 'pending')->sortBy('due_date')->first();
                
                return [
                    'id' => $loan->id,
                    'loan_id' => $formattedLoanId,
                    'loan_number' => $loan->loan_id,
                    'amount' => (float) $loan->amount,
                    'status' => ucfirst($loan->status),
                    'pending_amount' => $pendingAmount,
                    'paid_amount' => $totalPaid,
                    'type' => $loan->loanType->type ?? 'Personal Loan',
                    'purpose' => $loan->purpose_of_loan,
                    'start_date' => $loan->loan_start_date,
                    'due_date' => $loan->loan_due_date,
                    'terms' => $loan->loan_terms,
                    'term_period' => $loan->loan_term_period,
                    'interest_rate' => $loan->loanType->interest_rate ?? 12,
                    'monthly_emi' => $nextEmi ? (float) $nextEmi->total_amount : 0,
                    'next_emi_date' => $nextEmi ? $nextEmi->due_date : null,
                    'created_at' => $loan->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $loan->updated_at->format('Y-m-d H:i:s')
                ];
            });
            
            return response()->json([
                'success' => true,
                'loans' => $loansData,
                'total_loans' => $loans->count(),
                'summary' => [
                    'total_amount' => $loans->sum('amount'),
                    'active_loans' => $loans->whereIn('status', ['approved', 'disbursed'])->count(),
                    'pending_loans' => $loans->where('status', 'pending')->count(),
                    'completed_loans' => $loans->where('status', 'closed')->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch loans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific loan details
     */
    public function getLoanDetails(Request $request, $loanId)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        try {
            $loan = Loan::where('customer', $userData->id)
                ->where('loan_id', $loanId)
                ->with(['loanType', 'RepaymentSchedules', 'Repayments'])
                ->first();
                
            if (!$loan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan not found'
                ], 404);
            }
            
            $formattedLoanId = '#LON-' . str_pad($loan->loan_id, 4, '0', STR_PAD_LEFT);
            
            // Get repayment schedules
            $repaymentSchedules = $loan->RepaymentSchedules->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'due_date' => $schedule->due_date,
                    'principal_amount' => (float) ($schedule->principal_amount ?? 0),
                    'interest' => (float) ($schedule->interest ?? 0),
                    'penalty' => (float) ($schedule->penalty ?? 0),
                    'total_amount' => (float) $schedule->total_amount,
                    'status' => ucfirst($schedule->status),
                    'paid_amount' => (float) ($schedule->paid_amount ?? 0),
                    'payment_date' => $schedule->payment_date,
                    'installment_number' => $schedule->installment_number ?? 1
                ];
            });
            
            // Get transaction history
            $transactions = $loan->Repayments->map(function($repayment) {
                return [
                    'id' => $repayment->id,
                    'payment_date' => $repayment->payment_date,
                    'amount' => (float) $repayment->total_amount,
                    'principal' => (float) ($repayment->principal_amount ?? 0),
                    'interest' => (float) ($repayment->interest ?? 0),
                    'penalty' => (float) ($repayment->penalty ?? 0),
                    'status' => ucfirst($repayment->status ?? 'Completed'),
                    'payment_method' => $repayment->payment_method ?? 'Cash',
                    'reference_number' => $repayment->reference_number ?? '',
                    'created_at' => $repayment->created_at->format('Y-m-d H:i:s')
                ];
            });
            
            // Calculate loan summary
            $totalScheduled = $loan->RepaymentSchedules->sum('total_amount');
            $totalPaid = $loan->Repayments->sum('total_amount');
            $pendingAmount = max(0, $totalScheduled - $totalPaid);
            $nextPayment = $loan->RepaymentSchedules->where('status', 'pending')->sortBy('due_date')->first();
            
            $loanDetails = [
                'loan_id' => $formattedLoanId,
                'loan_number' => $loan->loan_id,
                'amount' => (float) $loan->amount,
                'status' => ucfirst($loan->status),
                'type' => $loan->loanType->type ?? 'Personal Loan',
                'purpose' => $loan->purpose_of_loan,
                'start_date' => $loan->loan_start_date,
                'due_date' => $loan->loan_due_date,
                'terms' => $loan->loan_terms,
                'term_period' => $loan->loan_term_period,
                'interest_rate' => $loan->loanType->interest_rate ?? 0,
                'total_scheduled' => $totalScheduled,
                'total_paid' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'next_payment' => $nextPayment ? [
                    'due_date' => $nextPayment->due_date,
                    'amount' => (float) $nextPayment->total_amount,
                    'days_remaining' => Carbon::parse($nextPayment->due_date)->diffInDays(now())
                ] : null,
                'created_at' => $loan->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $loan->updated_at->format('Y-m-d H:i:s')
            ];
            
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch loan details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to authenticate user with token
     */
    private function authenticateUser(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return ['success' => false, 'message' => 'Token required'];
            }
            
            $token = str_replace('Bearer ', '', $token);
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);
            
            if (count($parts) < 3) {
                return ['success' => false, 'message' => 'Invalid token'];
            }
            
            $userId = $parts[0];
            $user = User::find($userId);
            
            if (!$user || $user->type !== 'customer') {
                return ['success' => false, 'message' => 'Unauthorized'];
            }
            
            // Verify token in cache
            $cachedToken = Cache::get('pwa_token_' . $userId);
            if ($cachedToken !== $token) {
                return ['success' => false, 'message' => 'Invalid or expired token'];
            }
            
            return ['success' => true, 'user' => $user];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Authentication failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process EMI Payment
     */
    public function processEMIPayment(Request $request)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'schedule_id' => 'required|integer',
                'payment_method' => 'required|string',
                'amount' => 'required|numeric|min:0.01'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Find the repayment schedule
            $schedule = \App\Models\RepaymentSchedule::find($request->schedule_id);
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Repayment schedule not found'
                ], 404);
            }
            
            // Verify the schedule belongs to this customer's loan
            $loan = \App\Models\Loan::where('id', $schedule->loan_id)
                ->where('customer', $userData->id)
                ->first();
                
            if (!$loan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this repayment schedule'
                ], 403);
            }
            
            // Check if schedule is already paid
            if ($schedule->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This EMI has already been paid'
                ], 400);
            }
            
            // For demo purposes, mark as paid immediately
            // In production, you would integrate with payment gateway here
            $schedule->status = 'paid';
            $schedule->save();
            
            // Create a payment record (you may need to create this table)
            try {
                \DB::table('loan_payments')->insert([
                    'loan_id' => $loan->id,
                    'schedule_id' => $schedule->id,
                    'customer_id' => $userData->id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now(),
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                // If loan_payments table doesn't exist, just log it
                \Log::info('Payment record could not be created: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => [
                    'schedule_id' => $schedule->id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now()->toDateString(),
                    'status' => 'completed'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = $this->authenticateUser($request);
        if (!$user['success']) {
            return response()->json($user, 401);
        }

        $userData = $user['user'];
        
        // Remove token from cache
        Cache::forget('pwa_token_' . $userData->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}