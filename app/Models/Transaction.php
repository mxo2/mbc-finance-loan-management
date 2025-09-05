<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_time',
        'account_id',
        'account_number',
        'customer',
        'type',
        'amount',
        'status',
        'notes',
        'parent_id',
    ];
    public static $status = [
        'Pending' => 'Pending',
        'Completed' => 'Completed',
        'Cancelled' => 'Cancelled',
    ];
    public static $type = [
        'Deposit' => 'Deposit',
        'Withdraw' => 'Withdraw',
    ];
    public function Customers()
    {
        return $this->hasOne('App\Models\User', 'id', 'customer');
    }
    public function accountType()
    {
        return $this->hasOne('App\Models\AccountType', 'id', 'account_type');
    }
}
