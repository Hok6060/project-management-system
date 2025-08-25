<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Models\Loan;
use App\LoanManagement\Models\RepaymentSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoanPayoffController extends Controller
{
    /**
     * Display a listing of completed loans.
     */
    public function index()
    {
        $paidOffLoans = Loan::with('customer', 'loanType')
            ->where('status', 'completed')
            ->latest('updated_at')
            ->paginate(10);

        return view('loan-management.admin.payoffs.index', compact('paidOffLoans'));
    }

    /**
     * Show the form for creating a new loan payoff.
     */
    public function create()
    {
        // Fetch only active loans that can be paid off
        $activeLoans = Loan::with('customer')
            ->where('status', 'active')
            ->orderBy('loan_identifier')
            ->get();

        $systemDate = Setting::first()->system_date;

        return view('loan-management.admin.payoffs.create', compact('activeLoans', 'systemDate'));
    }

    /**
     * Calculate and display the payoff amount for a selected loan.
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'loan_id'     => ['required', 'exists:loans,id'],
            'payoff_date' => ['required', 'date'],
        ]);

        $loan       = Loan::with(['loanType'])->findOrFail($validated['loan_id']);
        $payoffDate = \Carbon\Carbon::parse($validated['payoff_date'])->startOfDay();

        $principalPaid = RepaymentSchedule::where('loan_id', $loan->id)->sum('principal_paid');

        $outstandingPrincipal = bcsub($loan->principal_amount, $principalPaid, 2);
        if (bccomp($outstandingPrincipal, '0.00', 2) === -1) $outstandingPrincipal = '0.00';

        $unpaidInterest = RepaymentSchedule::where('loan_id', $loan->id)
            ->whereDate('due_date', '<=', $payoffDate)
            ->get()
            ->reduce(function ($carry, $schedule) {
                $diff = bcsub($schedule->interest_component, $schedule->interest_paid, 2);
                return bcadd($carry, bccomp($diff, '0.00', 2) === 1 ? $diff : '0.00', 2);
            }, '0.00');

        $lastSettled = RepaymentSchedule::where('loan_id', $loan->id)
            ->whereIn('status', ['paid', 'paid_late'])
            ->whereDate('due_date', '<=', $payoffDate)
            ->orderBy('due_date', 'desc')
            ->first();

        $interestStartDate = $lastSettled
            ? \Carbon\Carbon::parse($lastSettled->due_date)
            : \Carbon\Carbon::parse($loan->approval_date);

        $daysToAccrue = max(0, $interestStartDate->diffInDays($payoffDate));
        $dailyRate    = bcdiv($loan->interest_rate, 100 * 365, 12);
        $perDiem      = bcmul($outstandingPrincipal, $dailyRate, 2);

        $dailyAccruedInterest = bcmul($perDiem, $daysToAccrue, 2);
        $accruedInterest      = bcadd($unpaidInterest, $dailyAccruedInterest, 2);

        $outstandingPenalties = RepaymentSchedule::where('loan_id', $loan->id)
            ->get()
            ->reduce(function ($carry, $s) {
                $diff = bcsub($s->penalty_amount, $s->penalty_paid, 2);
                return bcadd($carry, bccomp($diff, '0.00', 2) === 1 ? $diff : '0.00', 2);
            }, '0.00');

        $earlyPayoffPenalty = '0.00';
        if ($loan->loanType && (int) $loan->loanType->prepayment_penalty_period > 0) {
            $monthsFromApproval = \Carbon\Carbon::parse($loan->approval_date)->diffInMonths($payoffDate);
            if ($monthsFromApproval < (int) $loan->loanType->prepayment_penalty_period) {
                $earlyPayoffPenalty = $loan->loanType->prepayment_penalty_amount ?? '0.00';
            }
        }

        $totalPayoff = bcadd(
            bcadd(
                bcadd($outstandingPrincipal, $accruedInterest, 2),
                $outstandingPenalties,
                2
            ),
            $earlyPayoffPenalty,
            2
        );

        return view('loan-management.admin.payoffs.show', [
            'loan'                 => $loan,
            'payoffDate'           => $payoffDate,
            'currentBalance'       => $outstandingPrincipal,
            'accruedInterest'      => $accruedInterest,
            'outstandingPenalties' => $outstandingPenalties,
            'earlyPayoffPenalty'   => $earlyPayoffPenalty,
            'totalPayoff'          => $totalPayoff,
            'interestAccrualFrom'  => $interestStartDate,
            'perDiem'              => $perDiem,
        ]);
    }
}