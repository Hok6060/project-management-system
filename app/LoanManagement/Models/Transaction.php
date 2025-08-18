<?php

namespace App\LoanManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'repayment_schedule_id',
        'user_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'notes',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function repaymentSchedule()
    {
        return $this->belongsTo(RepaymentSchedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}