<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Repayment;
use App\Models\RepaymentSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Stripe;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Invoice;

class RepaymentController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage repayment')) {
            if (\Auth::user()->type == 'customer') {
                $loans = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->where('status', 'approved')->get()->pluck('id');
                $repayments = Repayment::where('parent_id', parentId())->whereIn('loan_id', $loans)->orderBy('payment_date', 'asc')->get();
            } else {
                $repayments = Repayment::where('parent_id', parentId())->orderBy('payment_date', 'asc')->get();
            }
            return view('repayments.index', compact('repayments'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function schedules()
    {
        if (\Auth::user()->can('manage repayment')) {

            if (\Auth::user()->type == 'customer') {
                $loans = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->where('status', 'approved')->get()->mapWithKeys(function ($loan) {
                    return [$loan->id => loanPrefix() . $loan->loan_id];
                })->prepend(__('Select Loan'), '')->toArray();
                $loan = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->where('status', 'approved')->get()->pluck('id');
                $schedules = RepaymentSchedule::where('parent_id', parentId())->whereIn('loan_id', $loan)->orderBy('due_date', 'asc')->get();
            } else {
                $loans = Loan::where('parent_id', parentId())->get()->mapWithKeys(function ($loan) {
                    return [$loan->id => loanPrefix() . $loan->loan_id];
                })->prepend(__('Select Loan'), '')->toArray();

                $schedules = RepaymentSchedule::where('parent_id', parentId())->orderBy('due_date', 'asc')->get();
            }
            return view('repayments.schedule', compact('schedules', 'loans'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create repayment')) {
            $loan = Loan::where('parent_id', parentId())->get();
            $loans = [];
            $loans[''] = __('Select Loan');
            foreach ($loan as $key => $value) {
                $loans[$value->id] = loanPrefix() . $value->loan_id;
            }

            return view('repayments.create', compact('loans'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {

        if (\Auth::user()->can('create repayment')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'loan_id' => 'required',
                    'payment_date' => 'required',
                    'principal_amount' => 'required',
                    'interest' => 'required',
                    'penality' => 'required',
                    'total_amount' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $repayment = new Repayment();
            $repayment->loan_id = $request->loan_id;
            $repayment->payment_date = $request->payment_date;
            $repayment->principal_amount = $request->principal_amount;
            $repayment->interest = $request->interest;
            $repayment->penality = $request->penality;
            $repayment->total_amount = $request->total_amount;
            $repayment->parent_id = parentId();
            $repayment->save();
            $installment = RepaymentSchedule::where('loan_id', $request->loan_id)->where('id', $request->schedule_id)->orderBy('created_at', 'DESC')->first();
            if ($installment) {
                $installment->penality = $request->penality;
                $installment->total_amount = $request->total_amount;
                $installment->status = 'Paid';
                $installment->save();
            }

            $module = 'repayment_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';
             if (!empty($notification)) {
		    $notification_responce = MessageReplace($notification, $repayment->id);
		    $datas = [
		        'subject' => $notification_responce['subject'],
		        'message' => $notification_responce['message'],
		        'module'  => $module,
		        'logo'    => $setting['company_logo'],
		    ];

		    $customer = Customer::where('user_id', $repayment->Loans->customer)->first();
		    $customerEmail = User::find($repayment->Loans->customer)->email;
		    $branchEmail = $customer->branch->email;
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
		            send_twilio_msg($customer->branch->phone_number, $notification_responce['sms_message']);
		        }
		    }
            }


            return redirect()->route('repayment.index')->with('success', __('Repayment successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Repayment $repayment)
    {
        //
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit repayment')) {

            $repayment = Repayment::find(decrypt($id));
            $loan = Loan::where('parent_id', parentId())->get();
            $loans = [];
            $loans[''] = __('Select Loan');
            foreach ($loan as $key => $value) {
                $loans[$value->id] = loanPrefix() . $value->loan_id;
            }

            return view('repayments.edit', compact('repayment', 'loans'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('create repayment')) {
            $repayment = Repayment::find(decrypt($id));
            $validator = \Validator::make(
                $request->all(),
                [
                    'loan_id' => 'required',
                    'payment_date' => 'required',
                    'principal_amount' => 'required',
                    'interest' => 'required',
                    'penality' => 'required',
                    'total_amount' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $repayment->loan_id = $request->loan_id;
            $repayment->payment_date = $request->payment_date;
            $repayment->principal_amount = $request->principal_amount;
            $repayment->interest = $request->interest;
            $repayment->penality = $request->penality;
            $repayment->total_amount = $request->total_amount;
            $repayment->save();

            return redirect()->back()->with('success', __('Repayment successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete repayment')) {
            $repayment = Repayment::find(decrypt($id));
            $repayment->delete();

            return redirect()->back()->with('success', __('Repayment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function getLoanInstallment(Request $request)
    {
        $installment = RepaymentSchedule::where('loan_id', $request->loan)
            ->where('status', 'Pending')
            ->first();

        if ($installment) {
            $loanType = $installment->Loans->loanType->penalties; // penalty rate in %
            $dueDate = $installment->due_date;
            $date = now();

            $amount = $installment->interest + $installment->installment_amount;

            if ($dueDate < $date) {
                $daysCount = \Carbon\Carbon::parse($dueDate)->diffInDays($date);
                $dailyPenalty = ($amount * $loanType) / 100; // per day penalty
                $totalPenalty = $daysCount * $dailyPenalty;
                $penAmount = number_format($totalPenalty, 2, '.', '');  // 2 decimals, no commas
                $penTotal = number_format($amount + $totalPenalty, 2, '.', '');

                $installment->penality = $penAmount;
                $installment->total_amount = $penTotal;
            }
        }

        if ($installment) {
            $installment->toArray();
            $response = [
                'status' => true,
                'installment' => $installment,
            ];
        } else {
            $response = [
                'status' => false,
            ];
        }
        return response()->json($response);
    }

    public function scheduleDestroy($id)
    {
        if (\Auth::user()->can('delete repayment')) {
            RepaymentSchedule::find(decrypt($id))->delete();
            return redirect()->back()->with('success', __('Repayment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function schedulesPayment($id)
    {
        if (\Auth::user()->can('repayment schedule payment')) {

            $schedules = RepaymentSchedule::find(decrypt($id));
            $settings = settings();

            $loanType = $schedules->Loans->loanType->penalties;
            $dueDate = $schedules->due_date;
            $date = now();
            $amount = $schedules->interest + $schedules->installment_amount;

            if ($dueDate < $date) {
                $daysCount = \Carbon\Carbon::parse($dueDate)->diffInDays($date);
                $penaltyPerDay = ($amount * $loanType) / 100;
                $totalPenalty = $daysCount * $penaltyPerDay;

                $schedules->penality = number_format($totalPenalty, 2, '.', '');
                $schedules->total_amount = number_format($amount + $totalPenalty, 2, '.', '');
            }

            $schedulePaymentSettings = invoicePaymentSettings($schedules->parent_id);

            return view('repayments.payment', compact('schedules', 'settings', 'schedulePaymentSettings'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function schedulesPaymentAP($id)
    {
        if (\Auth::user()->can('repayment schedule payment')) {

            $schedules = RepaymentSchedule::find(decrypt($id));

            return view('repayments.payment_check', compact('schedules'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loanFilter(Request $request)
    {
        $dateRange = $request->date;
        $loanID = $request->loan ?? '';
        $startDate = '';
        $endDate = '';

        if (\Auth::user()->type == 'customer') {
            $loans = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->where('status', 'approved')->get()->mapWithKeys(function ($loan) {
                return [$loan->id => loanPrefix() . $loan->loan_id];
            })->prepend(__('Select Loan'), '')->toArray();
        } else {
            $loans = Loan::where('parent_id', parentId())->get()->mapWithKeys(function ($loan) {
                return [$loan->id => loanPrefix() . $loan->loan_id];
            })->prepend('Select Loan', '')->toArray();
        }

        if (!empty($dateRange)) {
            [$start, $end] = explode(' - ', $dateRange);
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d');
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d');
        }

        $schedule = RepaymentSchedule::where('parent_id', parentId());
        if ($loanID) {
            $schedule->where('loan_id', $loanID);
        }
        if ($startDate && $endDate) {
            $schedule->whereBetween('due_date', [$startDate, $endDate]);
        }
        if (\Auth::user()->type == 'customer') {
            $loan_id = Loan::where('parent_id', parentId())->where('customer', \Auth::user()->id)->where('status', 'approved')->get()->pluck('id');
            // $schedule = RepaymentSchedule::where('parent_id', parentId());

            $schedules = $schedule->whereIn('loan_id', $loan_id)->orderBy('due_date', 'asc')->get();
        } else {
            $schedules = $schedule->orderBy('due_date', 'asc')->get();
        }
        return view('repayments.schedule', compact('schedules', 'loans', 'loanID', 'dateRange'));
    }

    public function schedulesPaymentStatus($id, $status)
    {

        $schedule = RepaymentSchedule::find($id);
        if (!empty($schedule)) {
            if ($status == 'Accept') {
                $schedule->status = 'Paid';
                if (!empty($schedule)) {
                    $repayment = new Repayment();
                    $repayment->loan_id = $schedule->loan_id;
                    $repayment->payment_date = $schedule->due_date;
                    $repayment->principal_amount = $schedule->installment_amount;
                    $repayment->interest = $schedule->interest;
                    $repayment->penality = $schedule->penality;
                    $repayment->total_amount = $schedule->total_amount;
                    $repayment->parent_id = parentId();
                    $repayment->save();
                }
            } else {
                $schedule->status = 'Pending';
            }
            $schedule->save();
        }

        return redirect()->back();
    }

    public function paymentSettings()
    {
        $paymentSetting = invoicePaymentSettings(parentId());
        return $paymentSetting;
    }

    public function stripePayment(Request $request, $ids)
    {
        $settings = $this->paymentSettings();
        $id = \Illuminate\Support\Facades\Crypt::decrypt($ids);
        $invoice = RepaymentSchedule::find($id);
        $amount = $request->amount;
        if ($invoice) {
            try {
                $transactionID = uniqid('', true);
                Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                $data = Stripe\Charge::create(
                    [
                        "amount" => 100 * $amount,
                        "currency" => $settings['CURRENCY'],
                        "source" => $request->stripeToken,
                        "description" => " Invoice - " . invoicePrefix() . $invoice->invoice_id,
                        "metadata" => ["order_id" => $transactionID],
                        'shipping' => [
                            'name' => $request->name,
                            'address' => [
                                'line1' => $request->state ?? 'NA',
                                'city' => $request->city ?? 'NA',
                                'postal_code' => $request->zipcode ?? '000000',
                                'country' => $request->country ?? 'NA',
                            ]
                        ],
                    ]
                );



                if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {

                    if ($data['status'] == 'succeeded') {

                        $payment['invoice_id'] = $invoice->id;
                        $payment['transaction_id'] = $transactionID;
                        $payment['payment_type'] = 'Stripe';
                        $payment['amount'] = $amount;
                        $payment['receipt'] = isset($data['receipt_url']) ? $data['receipt_url'] : '';
                        // $payment['notes'] = " Invoice - " . invoicePrefix() . $invoice->invoice_id;

                        RepaymentSchedule::addPayment($payment);
                        return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
                    } else {
                        return redirect()->back()->with('error', __('Your payment has failed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Transaction has been failed.'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->back()->with('error', __('Invoice is deleted.'));
        }
    }

    public function invoicePaypal(Request $request, $id)
    {
        $invoiceId = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $paypalSetting = $this->paymentSettings();

        if ($paypalSetting['paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('invoice.paypal.status', [$invoiceId, 'success'], ['amount' => $request->amount]),
                "cancel_url" => route('invoice.paypal.status', [$invoiceId, 'cancel'], ['amount' => $request->amount]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
                        "value" => $request->amount
                    ]
                ]
            ]
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()
                ->back()
                ->with('error', 'Something went wrong.');
        } else {
            return redirect()
                ->back()
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    public function invoicePaypalStatus(Request $request, $invoiceId, $status)
    {
        if ($status == 'success') {

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $transactionID = uniqid('', true);
            $invoice = RepaymentSchedule::find($invoiceId);
            $response = $provider->capturePaymentOrder($request['token']);
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $payment['invoice_id'] = $invoiceId;
                $payment['transaction_id'] = $transactionID;
                $payment['payment_type'] = 'Paypal';
                $payment['amount'] = $request->amount;
                $payment['receipt'] = '';
                // $payment['notes'] = " Invoice - " . invoicePrefix() . $invoice->invoice_id;

                RepaymentSchedule::addPayment($payment);
                return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
            } else {
                return redirect()
                    ->back()
                    ->with('error', $response['message'] ?? __('Something went wrong.'));
            }
        } else {
            return redirect()
                ->back()
                ->with('error', __('Transaction has been failed.'));
        }
    }

    public function banktransferPayment(Request $request, $id)
    {
        $invoiceId = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $validator = \Validator::make(
            $request->all(),
            [
                'receipt' => 'required',
                'amount' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $invoice = RepaymentSchedule::find($invoiceId);
        $transactionID = uniqid('', true);

        $payment['invoice_id'] = $invoice->id;
        $payment['transaction_id'] = $transactionID;
        $payment['payment_type'] = 'Bank Transfer';
        $payment['amount'] = $request->amount;
        // $payment['notes'] = $request->notes;
        if ($request->hasFile('receipt')) {
            $uploadResult = handleFileUpload($request->file('receipt'), 'upload/receipt/');

            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }
            $payment['receipt']  = $uploadResult['filename'];
        }

        RepaymentSchedule::addPayment($payment);
        return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
    }


    public function invoiceFlutterwave(Request $request, $invoice_id, $pay_id)
    {
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice = RepaymentSchedule::find($invoiceID);
        $paymentSetting = $this->paymentSettings();

        if ($invoice) {
            try {
                $detail = [
                    'txref' => $pay_id,
                    'SECKEY' => $paymentSetting['flutterwave_secret_key'],
                ];
                $url = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify";
                $headersData = ['Content-Type' => 'application/json'];
                $bodyData = \Unirest\Request\Body::json($detail);
                $responseData = \Unirest\Request::post($url, $headersData, $bodyData);

                if (!empty($responseData)) {
                    $responseData = json_decode($responseData->raw_body, true);
                }

                if (isset($responseData['status']) && $responseData['status'] == 'success') {
                    $amountPaid = $responseData['data']['amount'];
                    $expectedAmount = $request->query('amount'); // Get amount from request

                    if ($amountPaid < $expectedAmount) {
                        return redirect()->back()->with('error', __('Payment amount mismatch! Expected: ') . $expectedAmount);
                    }

                    $invoiceTransId = uniqid('', true);
                    RepaymentSchedule::addPayment([
                        'invoice_id' => $invoice->id,
                        'transaction_id' => $invoiceTransId,
                        'payment_type' => 'Flutterwave',
                        'amount' => $amountPaid,
                        // 'notes' => $request->notes ?? 'Flutterwave Payment',
                    ]);

                    return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
                } else {
                    return redirect()->back()->with('error', __('Transaction failed!'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }

    public function invoicePaystack(Request $request, $ids)
    {
        $payment_setting = $this->paymentSettings();
        $currency = $payment_setting['CURRENCY'] ?? 'USD';
        $id = Crypt::decrypt($ids);
        $invoice = RepaymentSchedule::find($id);

        if (!$invoice) {
            return response()->json([
                'flag' => 0,
                'message' => __('Invoice not found.')
            ]);
        }

        $amount = $request->amount;
        if ($amount <= 0) {
            return response()->json([
                'flag' => 0,
                'message' => __('Amount must be greater than 0.')
            ]);
        }

        return response()->json([
            'flag' => 1,
            'email' => auth()->user()->email,
            'total_price' => $amount,
            'currency' => $currency,
        ]);
    }


    public function invoicePaystackStatus(Request $request, $pay_id, $invoice_id_encrypted)
    {
        try {
            $invoice = RepaymentSchedule::find(Crypt::decrypt($invoice_id_encrypted));
            if (!$invoice) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }

            $secretKey = $this->paymentSettings()['paystack_secret_key'] ?? '';
            $verifyUrl = "https://api.paystack.co/transaction/verify/$pay_id";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $verifyUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $secretKey],
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = $response ? json_decode($response, true) : [];

            if (!($result['status'] ?? false) || ($result['data']['status'] !== 'success')) {
                return redirect()->back()->with('error', __('Transaction failed or cancelled.'));
            }

            $payment = [
                'invoice_id'     => $invoice->id,
                'transaction_id' => uniqid('', true),
                'payment_type'   => 'Paystack',
                'amount'         => $result['data']['amount'] / 100,
                'receipt'        => '',
                // 'notes'          => 'Paystack Payment',
            ];

            RepaymentSchedule::addPayment($payment);

            return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Something went wrong while verifying the payment.'));
        }
    }
}
