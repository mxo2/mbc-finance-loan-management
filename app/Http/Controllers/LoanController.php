<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\Loan;
use App\Models\LoanDocument;
use App\Models\LoanType;
use App\Models\Notification;
use App\Models\RepaymentSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage loan')) {
            if (\Auth::user()->type == 'customer') {
                // For customers, show available loan types to apply
                $loanTypes = LoanType::where('parent_id', parentId())->where('status', 1)->get();
                $myLoans = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->orderBy('id', 'DESC')->get();
                return view('loans.customer_index', compact('loanTypes', 'myLoans'));
            } else {
                $loans = Loan::where('parent_id', parentId())->orderBy('id', 'DESC')->get();
                return view('loans.index', compact('loans'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create loan')) {
            // Only admin/staff can create loans directly
            if (\Auth::user()->type == 'customer') {
                return redirect()->route('loan.index')->with('error', __('Customers cannot create loans directly. Please apply for available loan types.'));
            }
            
            $loanNumber = $this->loanNumber();
            $branch = Branch::where('parent_id', parentId())->get()->pluck('name', 'id');
            $branch->prepend(__('Select Branch'), '');
            $loanTypes = LoanType::where('parent_id', parentId())->get()->pluck('type', 'id');
            $loanTypes->prepend(__('Select Loan Type'), '');
            $documentTypes = DocumentType::where('parent_id', parentId())->get()->pluck('title', 'id');
            $documentTypes->prepend(__('Select Document Type'), '');
            $customers = User::where('parent_id', parentId())->where('type', 'customer')->get()->pluck('name', 'id');
            $customers->prepend(__('Select Customer'), '');
            $status = Loan::$status;
            $document_status = Loan::$document_status;
            $termPeroid = Loan::$termPeroid;
            return view('loans.create', compact('loanNumber', 'termPeroid', 'loanTypes', 'documentTypes', 'branch', 'customers', 'status', 'document_status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function apply($loanTypeId)
    {
        if (\Auth::user()->type != 'customer') {
            return redirect()->back()->with('error', __('Only customers can apply for loans.'));
        }

        $loanType = LoanType::find(decrypt($loanTypeId));
        if (!$loanType || $loanType->status != 1) {
            return redirect()->back()->with('error', __('Invalid or inactive loan type.'));
        }

        $loanNumber = $this->loanNumber();
        $branch = Branch::where('parent_id', parentId())->get()->pluck('name', 'id');
        $branch->prepend(__('Select Branch'), '');
        $documentTypes = DocumentType::where('parent_id', parentId())->get()->pluck('title', 'id');
        $documentTypes->prepend(__('Select Document Type'), '');
        $termPeroid = Loan::$termPeroid;
        
        return view('loans.apply', compact('loanNumber', 'loanType', 'branch', 'documentTypes', 'termPeroid'));
    }

    public function approve($id)
    {
        if (\Auth::user()->type == 'customer') {
            return redirect()->back()->with('error', __('Only administrators can approve loans.'));
        }

        $loan = Loan::find(decrypt($id));
        if (!$loan) {
            return redirect()->back()->with('error', __('Loan not found.'));
        }

        $loanTypes = LoanType::where('parent_id', parentId())->get()->pluck('type', 'id');
        $branch = Branch::where('parent_id', parentId())->get()->pluck('name', 'id');
        $customers = User::where('parent_id', parentId())->where('type', 'customer')->get()->pluck('name', 'id');
        $status = Loan::$status;
        $termPeroid = Loan::$termPeroid;
        
        return view('loans.approve', compact('loan', 'loanTypes', 'branch', 'customers', 'status', 'termPeroid'));
    }

    public function updateApproval(Request $request, $id)
    {
        if (\Auth::user()->type == 'customer') {
            return redirect()->back()->with('error', __('Only administrators can approve loans.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'status' => 'required',
                'amount' => 'required|numeric',
                'loan_terms' => 'required|numeric',
                'loan_term_period' => 'required',
            ]
        );
        
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $loan = Loan::find(decrypt($id));
        if (!$loan) {
            return redirect()->back()->with('error', __('Loan not found.'));
        }

        // Update loan details
        $loan->status = $request->status;
        $loan->amount = $request->amount;
        $loan->loan_terms = $request->loan_terms;
        $loan->loan_term_period = $request->loan_term_period;
        
        // Auto-calculate dates when loan is approved
        if ($request->status == 'approved') {
            $loan->loan_start_date = now()->format('Y-m-d');
            
            // Calculate due date based on loan terms
            $startDate = now();
            if ($loan->loan_term_period == 'days') {
                $loan->loan_due_date = $startDate->addDays($loan->loan_terms)->format('Y-m-d');
            } elseif ($loan->loan_term_period == 'weeks') {
                $loan->loan_due_date = $startDate->addWeeks($loan->loan_terms)->format('Y-m-d');
            } elseif ($loan->loan_term_period == 'months') {
                $loan->loan_due_date = $startDate->addMonths($loan->loan_terms)->format('Y-m-d');
            } elseif ($loan->loan_term_period == 'years') {
                $loan->loan_due_date = $startDate->addYears($loan->loan_terms)->format('Y-m-d');
            }
        } else {
            // For non-approved status (pending, rejected, etc.), clear the dates
            $loan->loan_start_date = null;
            $loan->loan_due_date = null;
        }
        
        $loan->notes = $request->admin_notes;
        $loan->save();

        // Generate repayment schedules when loan is approved
        if ($request->status == 'approved' && $loan->loan_start_date && $loan->loan_due_date) {
            // Delete existing schedules first
            RepaymentSchedule::where('loan_id', $loan->id)->delete();
            
            $installments = RepaymentSchedules($loan);
            foreach ($installments as $key => $values) {
                $repaymentSchedule = new RepaymentSchedule();
                $repaymentSchedule->loan_id = $values['loan_id'];
                $repaymentSchedule->due_date = $values['due_date'];
                $repaymentSchedule->installment_amount = $values['installment_amount'];
                $repaymentSchedule->interest = $values['interest'];
                $repaymentSchedule->total_amount = $values['total_amount'];
                $repaymentSchedule->penality = $values['penality'];
                $repaymentSchedule->status = $values['status'];
                $repaymentSchedule->parent_id = $values['parent_id'];
                $repaymentSchedule->save();
            }
        }

        $statusMessage = '';
        if ($request->status == 'approved') {
            $statusMessage = __('Loan application has been approved successfully.');
        } elseif ($request->status == 'rejected') {
            $statusMessage = __('Loan application has been rejected.');
        } else {
            $statusMessage = __('Loan status updated successfully.');
        }

        return redirect()->route('loan.index')->with('success', $statusMessage);
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create loan')) {
            // Different validation rules for customers vs admins
            if (\Auth::user()->type == 'customer') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'loan_type' => 'required',
                        'purpose_of_loan' => 'required',
                        'custom_purpose' => 'required_if:purpose_of_loan,Other',
                        'amount' => 'required|numeric',
                        'loan_term_period' => 'required',
                        'loan_terms' => 'required|numeric',
                        'referral_code' => 'required|string|min:3',
                        'aadhaar_card_front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                        'aadhaar_card_back' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                        'pan_card' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    ]
                );
            } else {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'loan_type' => 'required',
                        'loan_start_date' => 'required',
                        'loan_due_date' => 'required',
                        'purpose_of_loan' => 'required',
                        'amount' => 'required',
                        'loan_term_period' => 'required',
                        'loan_terms' => 'required',
                    ]
                );
            }
            
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            
            $loan = new Loan();
            $loan->loan_id = $this->loanNumber();
            $loan->loan_type = $request->loan_type;
            $loan->branch_id = $request->branch_id;
            $loan->customer = $request->customer;
            
            // Only set dates if provided (for admin) or leave null for customer applications
            $loan->loan_start_date = $request->loan_start_date;
            $loan->loan_due_date = $request->loan_due_date;
            
            $loan->amount = $request->amount;
            
            // Handle purpose of loan - use custom purpose if "Other" is selected
            if ($request->purpose_of_loan === 'Other' && !empty($request->custom_purpose)) {
                $loan->purpose_of_loan = $request->custom_purpose;
            } else {
                $loan->purpose_of_loan = $request->purpose_of_loan;
            }
            
            $loan->loan_terms = $request->loan_terms;
            $loan->loan_term_period = $request->loan_term_period;
            $loan->status = \Auth::user()->type == 'customer' ? 'pending' : 'draft';
            $loan->notes = $request->notes;
            $loan->referral_code = $request->referral_code;
            $loan->created_by = \Auth::user()->id;
            $loan->parent_id = parentId();
            
            // Calculate file charges based on loan type
            $loanType = LoanType::find($request->loan_type);
            if ($loanType && $loanType->file_charges > 0) {
                $loan->file_charges_amount = $loanType->calculateFileCharges($request->amount);
            }
            
            $loan->save();
            
            // Handle mandatory documents for customer applications
            if (\Auth::user()->type == 'customer') {
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
            }
            
            // Handle additional optional documents
            if ($loan && $request->document_type) {
                foreach ($request->document_type as $key => $value) {
                    if ($value) {
                        $loanDocument = new LoanDocument();
                        $loanDocument->loan_id = $loan->id;
                        $loanDocument->document_type = $request->document_type[$key];
                        $loanDocument->status = isset($request->document_status[$key]) ? $request->document_status[$key] : 'pending';
                        $loanDocument->notes = isset($request->description[$key]) ? $request->description[$key] : '';
                        if (isset($request->document[$key])) {
                            $uploadResult = handleFileUpload($request->document[$key], 'upload/loan_document/');
                            if ($uploadResult['flag'] == 1) {
                                $images = $uploadResult['filename'];
                            } else {
                                return redirect()->back()->with('error', $uploadResult['msg']);
                            }
                            $loanDocument->document = $images;
                        }
                        $loanDocument->save();
                    }
                }
            }
            
            // Only generate repayment schedules if loan has start and due dates (admin created loans)
            if ($loan->loan_start_date && $loan->loan_due_date) {
                $installments = RepaymentSchedules($loan);
                foreach ($installments as $key => $values) {

                    RepaymentSchedule::create([
                        'loan_id' => $loan->id,
                        'due_date' => $values['due_date'],
                        'installment_amount' => $values['installment_amount'],
                        'interest' => $values['interest'],
                        'penality' => 0,
                        'total_amount' => $values['total_amount'],
                        'status' => 'Pending',
                        'parent_id' => parentId()
                    ]);
                }
            }


            $module = 'loan_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';
             if (!empty($notification)) {
		    $notification_responce = MessageReplace($notification, $loan->id);
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
		        $response = commonEmailSend($to, $datas);
		        if ($response['status'] == 'error') {
		            $errorMessage = $response['message'];
		        }
		    }

		     if (!empty($notification) && $notification->enabled_sms == 1 && !empty($notification->sms_message)) {
		        $twilio_sid = getSettingsValByName('twilio_sid');
		        if (!empty($twilio_sid)) {
		            send_twilio_msg($loan->Customers->phone_number, $notification_responce['sms_message']);
		        }
		    }
            }

            return redirect()->route('loan.index')->with('success', __('Loan successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        $loan = Loan::find(decrypt($id));
        return view('loans.show', compact('loan'));
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit loan')) {
            $loan = Loan::find(decrypt($id));
            $loanNumber = $this->loanNumber();
            $branch = Branch::where('parent_id', parentId())->get()->pluck('name', 'id');
            $branch->prepend(__('Select Branch'), '');
            $loanTypes = LoanType::where('parent_id', parentId())->get()->pluck('type', 'id');
            $loanTypes->prepend(__('Select Loan Type'), '');
            $documentTypes = DocumentType::where('parent_id', parentId())->get()->pluck('title', 'id');
            $documentTypes->prepend(__('Select Document Type'), '');
            $customers = User::where('parent_id', parentId())->where('type', 'customer')->get()->pluck('name', 'id');
            $customers->prepend(__('Select Customer'), '');
            $status = Loan::$status;
            $document_status = Loan::$document_status;
            $termPeroid = Loan::$termPeroid;
            return view('loans.edit', compact('loan', 'termPeroid', 'loanNumber', 'loanTypes', 'documentTypes', 'branch', 'customers', 'status', 'document_status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit loan')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'loan_type' => 'required',
                    'loan_start_date' => 'required',
                    'loan_due_date' => 'required',
                    'purpose_of_loan' => 'required',
                    'custom_purpose' => 'required_if:purpose_of_loan,Other',
                    'amount' => 'required',
                    'loan_term_period' => 'required',
                    'loan_terms' => 'required',
                    'status' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $loan = Loan::find(decrypt($id));

            $ids = [];
            foreach ($loan->Documents as $key => $value) {
                $ids[$value->id] = $value->id;
            }



            $status = $loan->status != $request->status;

            $loan->loan_type = $request->loan_type;
            $loan->branch_id = $request->branch_id;
            $loan->customer = $request->customer;
            $loan->loan_start_date = $request->loan_start_date;
            $loan->loan_due_date = $request->loan_due_date;
            $loan->amount = $request->amount;
            
            // Handle purpose of loan - use custom purpose if "Other" is selected
            if ($request->purpose_of_loan === 'Other' && !empty($request->custom_purpose)) {
                $loan->purpose_of_loan = $request->custom_purpose;
            } else {
                $loan->purpose_of_loan = $request->purpose_of_loan;
            }
            
            $loan->loan_terms = $request->loan_terms;
            $loan->loan_term_period = $request->loan_term_period;
            $loan->status = $request->status;
            $loan->notes = $request->notes;
            $loan->save();
            if ($loan) {
                foreach ($request->document_type as $key => $value) {

                    if (isset($request->id[$key]) && in_array($request->id[$key], $ids)) {
                        $loanDocument = LoanDocument::find($request->id[$key]);
                        $loanDocument->document_type = $request->document_type[$key];
                        $loanDocument->status = $request->document_status[$key];
                        $loanDocument->notes = $request->description[$key];

                        if ($request->document && isset($request->document[$key])) {
                            $uploadResult = handleFileUpload($request->document[$key], 'upload/loan_document/');
                            if ($uploadResult['flag'] == 1) {
                                deleteOldFile($loanDocument->document, 'upload/loan_document/');
                                $images = $uploadResult['filename'];
                            } else {
                                return redirect()->back()->with('error', $uploadResult['msg']);
                            }
                            $loanDocument->document = $images;
                        }

                        $loanDocument->save();
                        unset($ids[$request->id[$key]]);
                    } else {
                        if ($value) {

                            $loanDocument = new LoanDocument();
                            $loanDocument->loan_id = $loan->id;
                            $loanDocument->document_type = $request->document_type[$key];
                            $loanDocument->status = $request->document_status[$key];
                            $loanDocument->notes = $request->description[$key];
                            if ($request->document && $request->document[$key]) {
                                $uploadResult = handleFileUpload($request->document[$key], 'upload/loan_document/');
                                if ($uploadResult['flag'] == 1) {
                                    $images = $uploadResult['filename'];
                                } else {
                                    return redirect()->back()->with('error', $uploadResult['msg']);
                                }
                                $loanDocument->document = $images;
                            }
                            $loanDocument->save();
                        }
                    }
                }

                if (count($ids) > 0) {
                    foreach ($ids as $key => $id) {
                        if ($id) {
                            $loanDocument = LoanDocument::find($id);
                            if ($loanDocument) {
                                deleteOldFile($loanDocument->document, 'upload/loan_document/');
                                $loanDocument->delete();
                            }
                        }
                    }
                }
            }

            if ($status) {


                $module = 'loan_status_update';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $setting = settings();
                $errorMessage = '';
                 if (!empty($notification)) {
		        $notification_responce = MessageReplace($notification, $loan->id);
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
		            $response = commonEmailSend($to, $datas);
		            if ($response['status'] == 'error') {
		                $errorMessage = $response['message'];
		            }
		        }

		       if (!empty($notification) && $notification->enabled_sms == 1 && !empty($notification->sms_message)) {
		            $twilio_sid = getSettingsValByName('twilio_sid');
		            if (!empty($twilio_sid)) {
		                send_twilio_msg($loan->Customers->phone_number, $notification_responce['sms_message']);
		            }
		        }
                }
            }

            $errorMessage = !empty($errorMessage) ? $errorMessage : '';
            return redirect()->route('loan.index')->with('success', __('Loan successfully updated.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('edit loan')) {

            $loan = Loan::find(decrypt($id));
            $documents = LoanDocument::where('loan_id', $loan->id)->get();
            if ($documents) {
                foreach ($documents as $document) {
                    if (!empty($document->document)) {
                        deleteOldFile($document->document, 'upload/loan_document/');
                    }
                }
            }
            LoanDocument::where('loan_id', $loan->id)->delete();
            Repayment::where('loan_id', $loan->id)->delete();
            RepaymentSchedule::where('loan_id', $loan->id)->delete();
            $loan->delete();
            return redirect()->route('loan.index')->with('success', __('Loan successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function loanNumber()
    {
        $latestLoan = Loan::where('parent_id', parentId())->latest()->first();
        if ($latestLoan == null) {
            return 1;
        } else {
            return $latestLoan->loan_id + 1;
        }
    }


    public function paymentRemind($id)
    {

        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $short_code = $notification->short_code;
        $notification->short_code = json_decode($notification->short_code);

        $Notifications = Notification::$modules;
        $notification_option = [];
        foreach ($Notifications as $key => $value) {
            $notification_option[$key] = $value['name'];
        }
        return view('loans.remind', compact('notification', 'notification_option', 'Notifications', 'id'));
    }

    public function paymentRemindData(Request $request, $id)
    {

        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $module = 'payment_reminder';

        $setting = settings();
        $errorMessage = '';
        if (!empty($notification) && $notification->enabled_email == 1) {
            $return['subject'] = $request->subject;
            $return['message'] = $request->message;
            $settings = settings();

            if (!empty($request->subject) && !empty($request->message)) {
                $search = [];
                $replace = [];

                $schedule = RepaymentSchedule::find(decrypt($id));
                $loan = Loan::find($schedule->loan_id);
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{customer_name}', '{branch_name}', '{due_date}', '{interest}', '{penality}', '{total_amount}', '{payment_status}', '{installment_amount}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $loan->Customers->name, $loan->branch->name, dateFormat($schedule->due_date), $schedule->interest, $schedule->penality, $schedule->total_amount, $schedule->status, $schedule->installment_amount];

                $return['subject'] = str_replace($search, $replace, $request->subject);
                $return['message'] = str_replace($search, $replace, $request->message);
            }

            $datas['subject'] = $return['subject'];
            $datas['message'] = $return['message'];
            $datas['module'] = $module;
            $datas['logo'] =  $setting['company_logo'];
            $to = $loan->Customers->email;
            $response = commonEmailSend($to, $datas);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
            }
        }



        return redirect()->back()->with('success', __('Email successfully sent.') . '</br>' . $errorMessage);
    }
}
