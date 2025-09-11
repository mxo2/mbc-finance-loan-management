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
    
    public function getPaymentFrequencyLabelAttribute()
    {
        return self::$paymentFrequency[$this->payment_frequency] ?? $this->payment_frequency;
    }
}
