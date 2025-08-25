<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Models\Loan;
use App\LoanManagement\Models\RepaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * Store the final payoff payment and close the loan.
     */
    public function store(Request $request, Loan $loan)
    {
        $payoffDetails = $this->calculatePayoffDetails($loan, Carbon::parse($request->payoff_date));
        $totalPayoff = $payoffDetails['totalPayoff'];

        if (bccomp($loan->credit_balance, $totalPayoff, 2) < 0) {
            return back()->with('error', 'Insufficient credit balance to pay off the loan. Please record more customer payments first.');
        }

        DB::transaction(function () use ($loan, $payoffDetails, $totalPayoff) {
            $loan->transactions()->create([
                'loan_id'        => $loan->id,
                'amount_paid'    => $totalPayoff,
                'penalty_paid'   => bcadd($payoffDetails['outstandingPenalties'], $payoffDetails['earlyPayoffPenalty'], 2),
                'interest_paid'  => $payoffDetails['accruedInterest'],
                'principal_paid' => $payoffDetails['currentBalance'],
                'payment_date'   => $payoffDetails['payoffDate'],
                'payment_method' => 'pay_off',
                'notes'          => 'Loan paid off ' . $payoffDetails['loan_identifier'],
            ]);

            $loan->repaymentSchedules()
                ->whereNotIn('status', ['paid', 'paid_late'])
                ->update([
                    'status' => 'paid',
                    'paid_on' => $payoffDetails['payoffDate'],
                ]);

            $loan->status = 'completed';
            $loan->credit_balance = bcsub($loan->credit_balance, $totalPayoff, 2);
            $loan->save();
        });

        return redirect()->route('loans.admin.payoffs.index')->with('success', "Loan {$loan->loan_identifier} has been successfully paid off and closed.");
    }

    /**
     * A private helper method to contain the calculation logic.
     */
    private function calculatePayoffDetails(Loan $loan, Carbon $payoffDate): array
    {
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

        return [
            'loan'                 => $loan,
            'loan_identifier'      => $loan->loan_identifier,
            'payoffDate'           => $payoffDate,
            'currentBalance'       => $outstandingPrincipal,
            'accruedInterest'      => $accruedInterest,
            'outstandingPenalties' => $outstandingPenalties,
            'earlyPayoffPenalty'   => $earlyPayoffPenalty,
            'totalPayoff'          => $totalPayoff,
            'interestAccrualFrom'  => $interestStartDate,
            'perDiem'              => $perDiem,
        ];
    }
}