<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'transaction_type',
        'amount',
        'payment_method',
        'transaction_reference',
        'bank_name', 
        'account_number',
        'transaction_date',
        'transaction_notes',
        'receipt_document',
        'status',
        'recorded_by',
        'verified_by',
        'verified_at',
        'parent_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'verified_at' => 'datetime'
    ];

    public static $transactionTypes = [
        'file_charges' => 'File Charges Payment',
        'loan_amount' => 'Loan Amount Transfer'
    ];

    public static $paymentMethods = [
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'cheque' => 'Cheque',
        'online_payment' => 'Online Payment',
        'upi' => 'UPI'
    ];

    public static $statuses = [
        'pending' => 'Pending Verification',
        'verified' => 'Verified',
        'rejected' => 'Rejected'
    ];

    // Relationships
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Helper methods
    public function getTransactionTypeLabel()
    {
        return self::$transactionTypes[$this->transaction_type] ?? $this->transaction_type;
    }

    public function getPaymentMethodLabel()
    {
        return self::$paymentMethods[$this->payment_method] ?? $this->payment_method;
    }

    public function getStatusLabel()
    {
        return self::$statuses[$this->status] ?? $this->status;
    }
}
