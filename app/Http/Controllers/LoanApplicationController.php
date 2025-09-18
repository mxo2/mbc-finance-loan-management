<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoanType;
use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\Loan;
use App\Models\LoanDocument;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoanApplicationController extends Controller
{
    /**
     * Display the modern loan homepage
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get all active loan types
        $loanTypes = LoanType::where('status', 1)
            ->where('parent_id', parentId())
            ->get();
            
        // Get branches for application form
        $branches = Branch::where('parent_id', parentId())->get();
        
        // Get user's loans and application count
        $activeApplications = 0;
        $myLoans = collect(); // Empty collection for guests
        
        if (Auth::check()) {
            $myLoans = Loan::where('customer', Auth::id())
                ->where('parent_id', parentId())
                ->orderBy('id', 'DESC')
                ->get();
                
                        $activeApplications = $myLoans->where('status', 'pending')->count();
        }
        
        return view('loans.customer_index', compact('loanTypes', 'branches', 'activeApplications', 'myLoans'));
    }

    /**
     * Display the loan application page with calculator (legacy)
     *
     * @return \Illuminate\View\View
     */
    public function application()
    {
        // Get all active loan types
        $loanTypes = LoanType::where('status', 1)
            ->where('parent_id', parentId())
            ->get();
            
        // Get branches for application form
        $branches = Branch::where('parent_id', parentId())->get();
        
        // Get document types for optional uploads
        $documentTypes = DocumentType::where('parent_id', parentId())->pluck('title', 'id');
        
        // Get default personal loan type
        $personalLoanType = LoanType::where('type', 'Personal Loan')
            ->where('status', 1)
            ->where('parent_id', parentId())
            ->first();
        
        // Fallback to first active loan type if no personal loan type exists
        if (!$personalLoanType && count($loanTypes) > 0) {
            $personalLoanType = $loanTypes->first();
        }
        
        // Set default values
        $defaults = [
            'min_loan_amount' => $personalLoanType ? $personalLoanType->min_loan_amount : 10000,
            'max_loan_amount' => $personalLoanType ? $personalLoanType->max_loan_amount : 30000,
            'default_loan_amount' => $personalLoanType ? 
                ($personalLoanType->min_loan_amount + $personalLoanType->max_loan_amount) / 2 : 20000,
            'interest_rate' => $personalLoanType ? $personalLoanType->interest_rate : 18,
            'interest_type' => $personalLoanType ? $personalLoanType->interest_type : 'fixed',
            'max_loan_term' => $personalLoanType ? $personalLoanType->max_loan_term : 96,
            'penalties' => $personalLoanType ? $personalLoanType->penalties : 1,
            'loan_types' => $loanTypes,
            'loan_type_id' => $personalLoanType ? $personalLoanType->id : null
        ];
        
        return view('loan.application', compact('defaults', 'branches', 'documentTypes', 'loanTypes'));
    }

    /**
     * Display the loan wizard for a specific loan type
     *
     * @param  int  $loanTypeId
     * @return \Illuminate\View\View
     */
    public function wizard($loanTypeId)
    {
        // Get the specific loan type
        $loanType = LoanType::where('id', $loanTypeId)
            ->where('status', 1)
            ->where('parent_id', parentId())
            ->first();
            
        if (!$loanType) {
            return redirect()->route('loan.index')->with('error', 'Loan type not found or inactive.');
        }
        
        // Get branches for application form
        $branches = Branch::where('parent_id', parentId())->get();
        
        return view('loans.wizard', compact('loanType', 'branches'));
    }
    
    /**
     * Calculate EMI based on provided parameters
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateEMI(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:1000',
            'loan_term' => 'required|numeric|min:1',
            'interest_rate' => 'sometimes|numeric',
            'loan_type_id' => 'sometimes|exists:loan_types,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        // Get values from request
        $principal = $request->input('loan_amount');
        $termMonths = $request->input('loan_term');
        
        // Get interest rate from loan type or request
        $interestRate = $request->input('interest_rate');
        $loanTypeId = $request->input('loan_type_id');
        
        if ($loanTypeId && !$interestRate) {
            $loanType = LoanType::find($loanTypeId);
            if ($loanType) {
                $interestRate = $loanType->interest_rate;
            }
        }
        
        // Default interest rate if not provided
        $interestRate = $interestRate ?? 18;
        
        // Convert annual interest rate to monthly and decimal
        $monthlyInterestRate = $interestRate / 100 / 12;
        
        // Calculate EMI using formula: EMI = P * r * (1 + r)^n / ((1 + r)^n - 1)
        $emi = $principal * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths) / 
               (pow(1 + $monthlyInterestRate, $termMonths) - 1);
        
        // Calculate total payment and interest
        $totalPayment = $emi * $termMonths;
        $interestPayment = $totalPayment - $principal;
        
        // Return response with calculated values
        return response()->json([
            'emi' => round($emi, 2),
            'total_interest' => round($interestPayment, 2),
            'total_amount' => round($totalPayment, 2),
            'loan_amount' => $principal,
            'loan_term' => $termMonths,
            'interest_rate' => $interestRate
        ]);
    }
    
    /**
     * Process the loan application submission from wizard
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function submitApplication(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'loan_type' => 'required|exists:loan_types,id',
            'amount' => 'required|numeric|min:1000',
            'loan_terms' => 'required|numeric|min:1',
            'loan_term_period' => 'required|in:days,weeks,months,years',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'pan_number' => 'required|string|size:10',
            'aadhaar_number' => 'required|string|size:14',
            'employment_type' => 'required|in:salaried,self_employed,business,freelancer',
            'monthly_income' => 'required|numeric|min:1',
            'company_name' => 'required|string|max:255',
            'work_experience' => 'required|numeric|min:0',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|size:6',
            'loan_purpose' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'aadhaar_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhaar_back' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_card' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'income_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $validator->errors()->first());
        }
        
        // Check if user is authenticated
        if (!Auth::check()) {
            // Store application data in session and redirect to login
            session(['loan_application_data' => $request->all()]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to continue with your loan application.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')->with('message', 'Please login to continue with your loan application.');
        }
        
        try {
            // Create a new loan application
            $loan = new Loan();
            $loan->loan_id = $this->loanNumber();
            $loan->loan_type = $request->loan_type;
            $loan->branch_id = $request->branch_id;
            $loan->customer = Auth::id();
            $loan->amount = $request->amount;
            $loan->purpose_of_loan = $request->loan_purpose;
            $loan->loan_terms = $request->loan_terms;
            $loan->loan_term_period = $request->loan_term_period;
            $loan->status = 'pending';
            
            // Store additional personal details in notes field
            $personalDetails = [
                'full_name' => $request->full_name,
                'date_of_birth' => $request->date_of_birth,
                'pan_number' => $request->pan_number,
                'aadhaar_number' => $request->aadhaar_number,
                'employment_type' => $request->employment_type,
                'monthly_income' => $request->monthly_income,
                'company_name' => $request->company_name,
                'work_experience' => $request->work_experience,
                'address' => $request->address,
                'city' => $request->city,
                'pincode' => $request->pincode,
            ];
            
            $loan->notes = json_encode($personalDetails);
            $loan->referral_code = $request->referral_code;
            $loan->created_by = Auth::id();
            $loan->parent_id = parentId();
            $loan->save();
            
            // Handle document uploads
            $documentUploads = [
                'aadhaar_front' => ['type' => 1, 'note' => 'Mandatory Aadhaar Card - Front Side'],
                'aadhaar_back' => ['type' => 1, 'note' => 'Mandatory Aadhaar Card - Back Side'],
                'pan_card' => ['type' => 3, 'note' => 'Mandatory PAN Card'],
                'income_proof' => ['type' => 2, 'note' => 'Income Proof Document']
            ];
            
            foreach ($documentUploads as $fileKey => $docInfo) {
                if ($request->hasFile($fileKey)) {
                    $loanDocument = new LoanDocument();
                    $loanDocument->loan_id = $loan->id;
                    $loanDocument->document_type = $docInfo['type'];
                    $loanDocument->status = 'pending';
                    $loanDocument->notes = $docInfo['note'];
                    
                    $uploadResult = handleFileUpload($request->file($fileKey), 'upload/loan_document/');
                    if ($uploadResult['flag'] == 1) {
                        $loanDocument->document = $uploadResult['filename'];
                        $loanDocument->save();
                    } else {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => ucfirst(str_replace('_', ' ', $fileKey)) . ' upload failed: ' . $uploadResult['msg']
                            ], 500);
                        }
                        return redirect()->back()->with('error', ucfirst(str_replace('_', ' ', $fileKey)) . ' upload failed: ' . $uploadResult['msg']);
                    }
                }
            }
            
            // Send notification
            $module = 'loan_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            
            if (!empty($notification)) {
                $notification_responce = MessageReplace($notification, $loan->id);
                $setting = settings();
                $datas = [
                    'subject' => $notification_responce['subject'],
                    'message' => $notification_responce['message'],
                    'module'  => $module,
                    'logo'    => $setting['company_logo'],
                ];

                $customerEmail = $request->email; // Use email from form since user might not have it set
                $branchEmail = $loan->branch ? $loan->branch->email : null;
                $to = array_filter([$customerEmail, $branchEmail]);

                if (!empty($notification) && $notification->enabled_email == 1 && !empty($to)) {
                    commonEmailSend($to, $datas);
                }

                if (!empty($notification) && $notification->enabled_sms == 1 && !empty($notification->sms_message)) {
                    $twilio_sid = getSettingsValByName('twilio_sid');
                    if (!empty($twilio_sid)) {
                        send_twilio_msg($request->phone, $notification_responce['sms_message']);
                    }
                }
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Loan application submitted successfully. Our team will review your application shortly.',
                    'loan_id' => $loan->loan_id
                ]);
            }
            
            return redirect()->route('loan.index')->with('success', __('Loan application submitted successfully. Our team will review your application shortly.'));
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing your application. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while processing your application. Please try again.');
        }
    }

    /**
     * Process the loan application submission (legacy)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitLegacyApplication(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'loan_type' => 'required|exists:loan_types,id',
            'purpose_of_loan' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'loan_term_period' => 'required|in:days,weeks,months,years',
            'loan_terms' => 'required|numeric|min:1',
            'branch_id' => 'required|exists:branches,id',
            'referral_code' => 'nullable|string|min:3',
            'aadhaar_card_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'aadhaar_card_back' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'pan_card' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $validator->errors()->first());
        }
        
        // Check if user is authenticated
        if (!Auth::check()) {
            // Store application data in session and redirect to login
            session(['loan_application_data' => $request->all()]);
            return redirect()->route('login')->with('message', 'Please login to continue with your loan application.');
        }
        
        // Create a new loan application
        $loan = new Loan();
        $loan->loan_id = $this->loanNumber();
        $loan->loan_type = $request->loan_type;
        $loan->branch_id = $request->branch_id;
        $loan->customer = Auth::id();
        $loan->amount = $request->amount;
        $loan->purpose_of_loan = $request->purpose_of_loan;
        $loan->loan_terms = $request->loan_terms;
        $loan->loan_term_period = $request->loan_term_period;
        $loan->status = 'pending';
        $loan->notes = $request->notes;
        $loan->referral_code = $request->referral_code;
        $loan->created_by = Auth::id();
        $loan->parent_id = parentId();
        $loan->save();
        
        // Handle document uploads
        if ($loan) {
            // Handle Aadhaar Card Front
            if ($request->hasFile('aadhaar_card_front')) {
                $aadhaarFrontDocument = new LoanDocument();
                $aadhaarFrontDocument->loan_id = $loan->id;
                $aadhaarFrontDocument->document_type = 1; // Aadhar Card ID from document_types table
                $aadhaarFrontDocument->status = 'pending';
                $aadhaarFrontDocument->notes = 'Mandatory Aadhaar Card - Front Side';
                
                $uploadResult = handleFileUpload($request->file('aadhaar_card_front'), 'upload/loan_document/');
                if ($uploadResult['flag'] == 1) {
                    $aadhaarFrontDocument->document = $uploadResult['filename'];
                    $aadhaarFrontDocument->save();
                } else {
                    return redirect()->back()->with('error', 'Aadhaar Card Front upload failed: ' . $uploadResult['msg']);
                }
            }
            
            // Handle Aadhaar Card Back
            if ($request->hasFile('aadhaar_card_back')) {
                $aadhaarBackDocument = new LoanDocument();
                $aadhaarBackDocument->loan_id = $loan->id;
                $aadhaarBackDocument->document_type = 1; // Aadhar Card ID from document_types table
                $aadhaarBackDocument->status = 'pending';
                $aadhaarBackDocument->notes = 'Mandatory Aadhaar Card - Back Side';
                
                $uploadResult = handleFileUpload($request->file('aadhaar_card_back'), 'upload/loan_document/');
                if ($uploadResult['flag'] == 1) {
                    $aadhaarBackDocument->document = $uploadResult['filename'];
                    $aadhaarBackDocument->save();
                } else {
                    return redirect()->back()->with('error', 'Aadhaar Card Back upload failed: ' . $uploadResult['msg']);
                }
            }
            
            // Handle PAN Card
            if ($request->hasFile('pan_card')) {
                $panDocument = new LoanDocument();
                $panDocument->loan_id = $loan->id;
                $panDocument->document_type = 3; // PAN CARD ID from document_types table
                $panDocument->status = 'pending';
                $panDocument->notes = 'Mandatory PAN Card';
                
                $uploadResult = handleFileUpload($request->file('pan_card'), 'upload/loan_document/');
                if ($uploadResult['flag'] == 1) {
                    $panDocument->document = $uploadResult['filename'];
                    $panDocument->save();
                } else {
                    return redirect()->back()->with('error', 'PAN Card upload failed: ' . $uploadResult['msg']);
                }
            }
            
            // Handle additional documents
            if ($request->document_type) {
                foreach ($request->document_type as $key => $value) {
                    if ($value && isset($request->document[$key])) {
                        $loanDocument = new LoanDocument();
                        $loanDocument->loan_id = $loan->id;
                        $loanDocument->document_type = $value;
                        $loanDocument->status = 'pending';
                        $loanDocument->notes = isset($request->description[$key]) ? $request->description[$key] : '';
                        
                        $uploadResult = handleFileUpload($request->document[$key], 'upload/loan_document/');
                        if ($uploadResult['flag'] == 1) {
                            $loanDocument->document = $uploadResult['filename'];
                            $loanDocument->save();
                        } else {
                            return redirect()->back()->with('error', 'Document upload failed: ' . $uploadResult['msg']);
                        }
                    }
                }
            }
        }
        
        // Send notification
        $module = 'loan_create';
        $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
        
        if (!empty($notification)) {
            $notification_responce = MessageReplace($notification, $loan->id);
            $setting = settings();
            $datas = [
                'subject' => $notification_responce['subject'],
                'message' => $notification_responce['message'],
                'module'  => $module,
                'logo'    => $setting['company_logo'],
            ];

            $customerEmail = $loan->Customers->email;
            $branchEmail = $loan->branch->email;
            $to = [$customerEmail, $branchEmail];

            if (!empty($notification) && $notification->enabled_email == 1) {
                commonEmailSend($to, $datas);
            }

            if (!empty($notification) && $notification->enabled_sms == 1 && !empty($notification->sms_message)) {
                $twilio_sid = getSettingsValByName('twilio_sid');
                if (!empty($twilio_sid)) {
                    send_twilio_msg($loan->Customers->phone_number, $notification_responce['sms_message']);
                }
            }
        }
        
        return redirect()->route('loan.index')->with('success', __('Loan application submitted successfully. Our team will review your application shortly.'));
    }
    
    /**
     * Process the loan application form
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function apply(Request $request)
    {
        // Validate form inputs
        $validator = \Validator::make(
            $request->all(),
            [
                'loan_amount' => 'required|numeric',
                'loan_term' => 'required|integer',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'required|string|max:20',
            ]
        );
        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first())->withInput();
        }
        
        // Store loan application in session for next steps
        $request->session()->put('loan_application', [
            'loan_amount' => $request->loan_amount,
            'loan_term' => $request->loan_term,
            'interest_rate' => $request->interest_rate,
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);
        
        // Redirect to next step in application process
        return redirect()->route('loan.documents')->with('success', __('Loan application details saved. Please upload your documents.'));
    }
    
    /**
     * Get calculator data for AJAX requests
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalculatorData(Request $request)
    {
        // Validate request
        $validator = \Validator::make(
            $request->all(),
            [
                'loan_amount' => 'required|numeric',
                'loan_term' => 'required|integer',
                'interest_rate' => 'required|numeric',
            ]
        );
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        
        // Get values from request
        $principal = $request->loan_amount;
        $termMonths = $request->loan_term;
        $annualInterestRate = $request->interest_rate;
        
        // Convert annual interest rate to monthly and decimal
        $monthlyInterestRate = $annualInterestRate / 100 / 12;
        
        // Calculate EMI using formula: EMI = P * r * (1 + r)^n / ((1 + r)^n - 1)
        $emi = $principal * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $termMonths) / 
               (pow(1 + $monthlyInterestRate, $termMonths) - 1);
        
        // Calculate total payment and interest
        $totalPayment = $emi * $termMonths;
        $interestPayment = $totalPayment - $principal;
        
        // Calculate percentages for chart
        $principalPercentage = round(($principal / $totalPayment) * 100);
        $interestPercentage = round(($interestPayment / $totalPayment) * 100);
        
        // Return response with calculated values
        return response()->json([
            'emi' => round($emi, 2),
            'total_interest' => round($interestPayment, 2),
            'total_amount' => round($totalPayment, 2),
            'principal_percentage' => $principalPercentage,
            'interest_percentage' => $interestPercentage
        ]);
    }
    
    /**
     * Generate a loan number
     *
     * @return int
     */
    private function loanNumber()
    {
        $latestLoan = Loan::where('parent_id', parentId())->latest()->first();
        if ($latestLoan == null) {
            return 1;
        } else {
            return $latestLoan->loan_id + 1;
        }
    }
}