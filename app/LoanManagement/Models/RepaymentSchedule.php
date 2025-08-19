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
        'penalty_amount',
        'amount_paid',
        'penalty_paid',
        'interest_paid',
        'principal_paid',
        'status',
        'paid_on',
        'last_penalty_date',
    ];

    /**
     * Get the loan that this schedule belongs to.
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}