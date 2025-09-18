<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    use HasFactory;
    protected $fillable=[
        'type',
        'min_loan_amount',
        'max_loan_amount',
        'interest_type',
        'interest_rate',
        'max_loan_term',
        'loan_term_period',
        'payment_frequency',
        'payment_day',
        'auto_start_date',
        'penalties',
        'penalty_type',
        'file_charges',
        'file_charges_type',
        'status',
        'notes',
        'parent_id',
    ];

    public static $interestType=[
        'onetime_payment'=>'Onetime Payment',
        'mortgage_amortization'=>'Mortgage Amortization',
        'fixed_rate'=>'Fixed Rate',
        'reducing_amount'=>'Reducing Amount',
        'flat_rate'=>'Flat Rate',
        'simple'=>'Simple Interest',
    ];

    public static $termPeroid=[
        'years'=>'Year',
        'months'=>'Month',
        'weeks'=>'Week',
        'days'=>'Day',
    ];
    
    public static $paymentFrequency=[
        'daily'=>'Daily',
        'weekly'=>'Weekly', 
        'monthly'=>'Monthly',
        'yearly'=>'Yearly',
    ];
    
    public static $penaltyType=[
        'percentage'=>'Percentage (%)',
        'fixed'=>'Fixed Amount',
    ];
    
    public static $fileChargesType=[
        'percentage'=>'Percentage (%)',
        'fixed'=>'Fixed Amount',
    ];
    
    public function getPaymentFrequencyLabelAttribute()
    {
        return self::$paymentFrequency[$this->payment_frequency] ?? $this->payment_frequency;
    }
    
    public function getPenaltyTypeLabelAttribute()
    {
        return self::$penaltyType[$this->penalty_type] ?? $this->penalty_type;
    }
    
    public function getFileChargesTypeLabelAttribute()
    {
        return self::$fileChargesType[$this->file_charges_type] ?? $this->file_charges_type;
    }
    
    public function calculateFileCharges($loanAmount)
    {
        if ($this->file_charges_type === 'percentage') {
            return ($loanAmount * $this->file_charges) / 100;
        }
        return $this->file_charges;
    }
}
