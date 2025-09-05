<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    use HasFactory;
    protected $fillable=[
        'loan_id',
        'payment_date',
        'principal_amount',
        'interest',
        'penality',
        'total_amount',
        'parent_id',
    ];
    public function Loans()
    {
        return $this->hasOne('App\Models\Loan', 'id', 'loan_id');
    }
}
