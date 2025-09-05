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
}
