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
        'purpose_of_loan',
        'loan_terms',
        'loan_term_period',
        'status',
        'notes',
        'created_by',
        'parent_id',
    ];

    public static $status = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'disbursed' => 'Disbursed',
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
}
