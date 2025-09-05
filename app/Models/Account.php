<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_number',
        'customer',
        'account_type',
        'status',
        'balance',
        'notes',
        'parent_id',
    ];
    public static $status = [
        'Active' => 'Active',
        'Inactive' => 'Inactive',
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
