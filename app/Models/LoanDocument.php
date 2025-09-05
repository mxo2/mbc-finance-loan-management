<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDocument extends Model
{
    use HasFactory;
    protected $fillable=[
        'loan_id',
        'document_type',
        'document',
        'status',
        'notes',
    ];

    public function types()
    {
        return $this->hasOne('App\Models\DocumentType','id','document_type');
    }
}
