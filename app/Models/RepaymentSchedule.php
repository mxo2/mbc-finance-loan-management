<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepaymentSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_id',
        'due_date',
        'installment_amount',
        'interest',
        'penality',
        'total_amount',
        'status',
        'transaction_id',
        'payment_type',
        'receipt',
        'parent_id',
    ];
    public function Loans()
    {
        return $this->hasOne('App\Models\Loan', 'id', 'loan_id');
    }


    public static function addPayment($data)
    {

        $payment = RepaymentSchedule::find($data['invoice_id']);

        $amount = $payment->interest + $payment->installment_amount;
        $penality = $data['amount'] - $amount;
        $payment->penality = $penality;
        $payment->total_amount = $data['amount'];
        $payment->payment_type = $data['payment_type'];
        $payment->transaction_id = $data['transaction_id'];
        $payment->receipt = !empty($data['receipt']) ? $data['receipt'] : '';
        if ($data['payment_type'] == 'Bank Transfer') {
            $payment->status = 'In Process';
        } else {
            if (!empty($payment)) {
                $repayment = new Repayment();
                $repayment->loan_id = $payment->loan_id;
                $repayment->payment_date = $payment->due_date;
                $repayment->principal_amount = $payment->installment_amount;
                $repayment->interest = $payment->interest;
                $repayment->penality = $penality;
                $repayment->total_amount = $payment->total_amount;
                $repayment->parent_id = parentId();
                $repayment->save();
            }
            $payment->status = 'Paid';
        }
        $payment->save();
        // $payment->invoice_id = $data['invoice_id'];
        // $payment->amount = $data['amount'];
        // $payment->payment_date = date('Y-m-d');
        // $payment->notes = $data['notes'];;
        // $payment->parent_id = parentId();
        // $invoice = Invoice::find($data['invoice_id']);
        // if ($invoice->getInvoiceDueAmount() <= 0) {
        //     $status = 'paid';
        // } else {
        //     $status = 'partial_paid';
        // }
        // Invoice::statusChange($invoice->id, $status);
    }
}
