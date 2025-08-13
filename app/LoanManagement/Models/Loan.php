<?php

namespace App\LoanManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_identifier',
        'loan_type_id',
        'customer_id',
        'loan_officer_id',
        'principal_amount',
        'interest_rate',
        'term',
        'payment_frequency',
        'interest_free_periods',
        'status',
        'application_date',
        'approval_date',
        'first_payment_date',
    ];

    /**
     * Get the loan type associated with the loan.
     */
    public function loanType()
    {
        return $this->belongsTo(LoanType::class);
    }

    /**
     * Get the customer for the loan.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the loan officer for the loan.
     */
    public function loanOfficer()
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    /**
     * Get the repayment schedule for the loan.
     */
    public function repaymentSchedules()
    {
        return $this->hasMany(RepaymentSchedule::class)->orderBy('payment_number', 'asc');
    }
}