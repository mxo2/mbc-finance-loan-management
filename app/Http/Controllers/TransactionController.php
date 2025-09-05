<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage transaction')) {
            if (\Auth::user()->type == 'customer') {
                $transactions = Transaction::where('parent_id', parentId())->where('customer', \Auth::user()->id)->get();
            } else {
                $transactions = Transaction::where('parent_id', parentId())->get();
            }
            return view('transaction.index', compact('transactions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create transaction')) {
            $customers = User::where('parent_id', parentId())->where('type', 'customer')->get()->pluck('name', 'id');
            $customers->prepend(__('Select Customer'), '');
            $status = Transaction::$status;
            $type = Transaction::$type;
            return view('transaction.create', compact('customers', 'status', 'type'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create transaction')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'customer' => 'required',
                    'account_number' => 'required',
                    'type' => 'required',
                    'status' => 'required',
                    'amount' => 'required',
                    'date_time' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $transaction = new Transaction();
            $transaction->customer = $request->customer;
            $transaction->account_id = $request->account;

            $transaction->account_number = $request->account_number;
            $transaction->type = $request->type;
            $transaction->status = $request->status;
            $transaction->amount = $request->amount;
            $transaction->date_time = $request->date_time;
            $transaction->notes = $request->notes;
            $transaction->parent_id = parentId();
            $transaction->save();

            $data['type'] = $request->type;
            $data['amount'] = $request->amount;
            $data['account'] = $request->account;
            accountTransaction($data);

            $module = 'transaction_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';
 		if (!empty($notification)) {
		    $notification_responce = MessageReplace($notification, $transaction->id);
		    if (!empty($notification) && $notification->enabled_email == 1) {
		        $datas = [
		            'subject' => $notification_responce['subject'],
		            'message' => $notification_responce['message'],
		            'module'  => $module,
		            'logo'    => $setting['company_logo'],
		        ];


		        $to = $transaction->Customers->email;
		        $response = commonEmailSend($to, $datas);
		        if ($response['status'] == 'error') {
		            $errorMessage = $response['message'];
		        }
		    }

		    if (!empty($notification) && $notification->enabled_sms == 1 && !empty($notification->sms_message)) {
		        $twilio_sid = getSettingsValByName('twilio_sid');
		        if (!empty($twilio_sid)) {
		            send_twilio_msg($transaction->Customers->phone_number, $notification_responce['sms_message']);
		        }
		    }
            }


            return redirect()->route('transaction.index')->with('success', __('Transaction successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Transaction $transaction)
    {
        //
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit transaction')) {
            $transaction = Transaction::find(decrypt($id));
            $customers = User::where('parent_id', parentId())->where('type', 'customer')->get()->pluck('name', 'id');
            $customers->prepend(__('Select Customer'), '');
            $status = Transaction::$status;
            $type = Transaction::$type;
            return view('transaction.edit', compact('transaction', 'customers', 'status', 'type'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit transaction')) {
            $transaction = Transaction::find(decrypt($id));
            $validator = \Validator::make(
                $request->all(),
                [
                    'customer' => 'required',
                    'account_number' => 'required',
                    'type' => 'required',
                    'status' => 'required',
                    'amount' => 'required',
                    'date_time' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $preRecord = Transaction::find($transaction->id);
            $transaction->customer = $request->customer;
            $transaction->account_id = $request->account;
            $transaction->account_number = $request->account_number;
            $transaction->type = $request->type;
            $transaction->status = $request->status;
            $transaction->amount = $request->amount;
            $transaction->date_time = $request->date_time;
            $transaction->notes = $request->notes;
            $transaction->save();

            if ($preRecord->type == 'Withdraw') {
                $data1['type'] = 'Deposit';
            } else {
                $data1['type'] = 'Withdraw';
            }

            $data1['amount'] = $preRecord->amount;
            $data1['account'] = $preRecord->account_id;
            accountTransaction($data1);

            $data['type'] = $request->type;
            $data['amount'] = $request->amount;
            $data['account'] = $request->account;

            accountTransaction($data);
            return redirect()->route('transaction.index')->with('success', __('Transaction successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('edit transaction')) {
            $transaction = Transaction::find(decrypt($id));
            if ($transaction->type == 'Withdraw') {
                $data['type'] = 'Deposit';
            } else {
                $data['type'] = 'Withdraw';
            }
            $data['amount'] = $transaction->amount;
            $data['account'] = $transaction->account_id;
            accountTransaction($data);

            $transaction->delete();
            return redirect()->route('transaction.index')->with('success', __('Transaction successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    function getAccount(Request $request)
    {
        $account = Account::select('id', 'account_number')->where('parent_id', parentId())->where('customer', $request->customer)->first();

        $response = [
            'status' => true,
            'account' => accountPrefix() . $account->account_number,
            'account_id' => $account->id,
        ];
        return response()->json($response);
    }
}
