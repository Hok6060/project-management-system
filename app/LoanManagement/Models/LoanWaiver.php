<?php

namespace App\LoanManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanWaiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'repayment_schedule_id',
        'requested_by_user_id',
        'approved_by_user_id',
        'waiver_type',
        'amount',
        'reason',
        'status',
        'processed_at',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function schedule()
    {
        return $this->belongsTo(RepaymentSchedule::class, 'repayment_schedule_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}