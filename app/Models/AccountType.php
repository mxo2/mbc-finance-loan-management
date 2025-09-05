<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;
    protected $fillable=[
        'title',
        'interest_rate',
        'interest_duration',
        'min_maintain_amount',
        'maintenance_charges',
        'charges_deduct_month',
        'parent_id',
    ];
    public static $termPeroid=[
        'year'=>'Year',
        'month'=>'Month',
        'week'=>'Week',
        'day'=>'Day',
    ];
}
