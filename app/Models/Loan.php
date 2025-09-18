<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    protected $fillable = [
        'loan_id',
        'loan_type',
        'customer',
        'loan_start_date',
        'loan_due_date',
        'amount',
        'file_charges_amount',
        'file_charges_status',
        'file_charges_paid_at',
        'disbursement_status',
        'disbursed_at',
        'disbursement_notes',
        'disbursed_by',
        'purpose_of_loan',
        'loan_terms',
        'loan_term_period',
        'status',
        'notes',
        'referral_code',
        'created_by',
        'parent_id',
    ];

    public static $status = [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'file_charges_pending' => 'File Charges Pending',
        'disbursement_pending' => 'Disbursement Pending',
        'disbursed' => 'Disbursed',
        'active' => 'Active',
        'closed' => 'Closed',
    ];

    public static $fileChargesStatus = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'waived' => 'Waived',
    ];

    public static $disbursementStatus = [
        'pending' => 'Pending',
        'disbursed' => 'Disbursed',
    ];

    protected $casts = [
        'file_charges_paid_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];
    public static $document_status = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];
    public static $termPeroid = [
        'months' => 'Month',
        'years' => 'Year',
        'weeks' => 'Week',
        'days' => 'Day',
    ];
    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch_id');
    }
    public function loanType()
    {
        return $this->hasOne('App\Models\LoanType', 'id', 'loan_type');
    }
    public function Customers()
    {
        return $this->hasOne('App\Models\User', 'id', 'customer');
    }
    public function Documents()
    {
        return $this->hasMany('App\Models\LoanDocument', 'loan_id', 'id');
    }
    public function Repayments()
    {
        return $this->hasMany('App\Models\Repayment', 'loan_id', 'id');
    }
    public function RepaymentSchedules()
    {
        return $this->hasMany('App\Models\RepaymentSchedule', 'loan_id', 'id');
    }
    public function createdByName()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function disbursements()
    {
        return $this->hasMany(LoanDisbursement::class);
    }

    public function fileChargesPayment()
    {
        return $this->hasOne(LoanDisbursement::class)->where('transaction_type', 'file_charges');
    }

    public function loanAmountTransfer()
    {
        return $this->hasOne(LoanDisbursement::class)->where('transaction_type', 'loan_amount');
    }

    public function disbursedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'disbursed_by');
    }

    // Helper methods
    public function canPayFileCharges()
    {
        return $this->status === 'approved' && $this->file_charges_status === 'pending';
    }

    public function canDisburse()
    {
        return $this->status === 'approved' && 
               ($this->file_charges_status === 'paid' || $this->file_charges_status === 'waived') && 
               $this->disbursement_status === 'pending';
    }

    public function isDisbursed()
    {
        return $this->disbursement_status === 'disbursed';
    }
}
