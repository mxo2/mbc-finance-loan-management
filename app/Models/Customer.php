<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable=[
        'user_id',
        'customer_id',
        'branch_id',
        'gender',
        'dob',
        'marital_status',
        'profession',
        'company',
        'city',
        'state',
        'country',
        'zip_code',
        'address',
        'notes',
        'parent_id',
    ];

    public static $gender=[
        'Male'=>'Male',
        'Female'=>'Female',
    ];

    public static $maritalStatus=[
        'Unmarried'=>'Unmarried',
        'Married'=>'Married',
    ];

    public function branch()
    {
        return $this->hasOne('App\Models\Branch','id','branch_id');
    }


}
