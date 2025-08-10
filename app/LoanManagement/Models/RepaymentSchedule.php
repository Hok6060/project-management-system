<?php

namespace App\LoanManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'payment_number',
        'due_date',
        'payment_amount',
        'principal_component',
        'interest_component',
        'remaining_balance',
        'status',
        'paid_on',
    ];

    /**
     * Get the loan that this schedule belongs to.
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}