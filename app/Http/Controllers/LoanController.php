<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DocumentType;
use App\Models\Loan;
use App\Models\LoanDocument;
use App\Models\LoanType;
use App\Models\Notification;
use App\Models\Repayment;
use App\Models\RepaymentSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class LoanController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage loan')) {
            if (\Auth::user()->type == 'customer') {
                $loans = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->orderBy('id', 'DESC')->get();
            } else {
                $loans = Loan::where('parent_id', parentId())->orderBy('id', 'DESC')->get();
            }
            return view('loans.index', compact('loans'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create loan')) {
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


    public function store(Request $request)
    {
        if (\Auth::user()->can('create loan')) {
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
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $loan = new Loan();
            $loan->loan_id = $this->loanNumber();
            $loan->loan_type = $request->loan_type;
            $loan->branch_id = $request->branch_id;
            $loan->customer = $request->customer;
            $loan->loan_start_date = $request->loan_start_date;
            $loan->loan_due_date = $request->loan_due_date;
            $loan->amount = $request->amount;
            $loan->purpose_of_loan = $request->purpose_of_loan;
            $loan->loan_terms = $request->loan_terms;
            $loan->loan_term_period = $request->loan_term_period;
            $loan->status = 'draft';
            $loan->notes = $request->notes;
            $loan->created_by = \Auth::user()->id;
            $loan->parent_id = parentId();
            $loan->save();
            if ($loan) {
                foreach ($request->document_type as $key => $value) {
                    if ($value) {
                        $loanDocument = new LoanDocument();
                        $loanDocument->loan_id = $loan->id;
                        $loanDocument->document_type = $request->document_type[$key];
                        $loanDocument->status = $request->document_status[$key];
                        $loanDocument->notes = ($request->description[$key]) ? $request->description[$key] : '';
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
            $loan->purpose_of_loan = $request->purpose_of_loan;
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
