<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanCycle extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'frequency',
        'payment_day',
        'is_active',
        'parent_id',
    ];
    
    public static $frequencies = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];
    
    public function getFrequencyLabelAttribute()
    {
        return self::$frequencies[$this->frequency] ?? $this->frequency;
    }
}
