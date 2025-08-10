<?php

namespace App\LoanManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'calculation_type',
        'min_interest_rate',
        'max_interest_rate',
        'min_term',
        'max_term',
        'is_active',
    ];
}