<?php

namespace App\LoanManagement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Models\RepaymentSchedule;
use App\LoanManagement\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEodJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('Starting background EOD process...');

        $settings = Setting::first();
        if (!$settings) {
            Log::error('System settings not found. Aborting EOD process.');
            return;
        }

        $runDate = Carbon::parse($settings->system_date)->startOfDay();
        $nextDate = $runDate->copy()->addDay()->startOfDay();
        Log::info("Processing EOD for {$runDate->toDateString()} -> {$nextDate->toDateString()}");

        DB::transaction(function () use ($runDate, $nextDate, $settings) {
            $this->autoPayZeroInstallments($nextDate);
            $this->applyCreditBalance($runDate, $nextDate);
            $this->processPenalties($nextDate);
            $this->advanceSystemDate($settings, $nextDate);
        });

        Log::info('EOD process complete.');
    }

    protected function autoPayZeroInstallments(Carbon $nextDate)
    {
        $zeroPayments = RepaymentSchedule::with('loan')
            ->where('status', 'pending')
            ->where('payment_amount', '=', 0)
            ->whereDate('due_date', '<', $nextDate)
            ->get();

        foreach ($zeroPayments as $payment) {
            $payment->status   = 'paid';
            $payment->paid_on  = $payment->due_date;
            $payment->save();

            $this->line(sprintf(
                'Auto-paid $0 installment: payment #%s for loan %s (due %s).',
                $payment->payment_number,
                $payment->loan->loan_identifier,
                Carbon::parse($payment->due_date)->toDateString()
            ));
        }
    }

    protected function applyCreditBalance(Carbon $runDate, Carbon $nextDate)
    {
        $pendingPayments = RepaymentSchedule::with('loan')
            ->whereIn('status', ['pending', 'due', 'late', 'partially_paid'])
            ->orderBy('payment_number')
            ->get();

        $transactionsToday = Transaction::whereDate('payment_date', $runDate->toDateString())
            ->get()
            ->groupBy('loan_id');

        $processedLoanIds = [];

        foreach ($pendingPayments as $payment) {
            $loan = $payment->loan;
            if (!$loan) continue;

            if (isset($processedLoanIds[$loan->id])) continue;

            $dueDate = Carbon::parse($payment->due_date);

            $hasZeroBefore = RepaymentSchedule::where('loan_id', $loan->id)
                ->where('status', 'pending')
                ->where('payment_number', '<', $payment->payment_number)
                ->where('payment_amount', '=', 0)
                ->exists();

            if ($hasZeroBefore) continue;

            $outPenalty   = bcsub($payment->penalty_amount   ?? '0.00', $payment->penalty_paid   ?? '0.00', 2);
            if (bccomp($outPenalty, '0.00', 2) < 0) $outPenalty = '0.00';

            $outInterest  = bcsub($payment->interest_component ?? '0.00', $payment->interest_paid  ?? '0.00', 2);
            if (bccomp($outInterest, '0.00', 2) < 0) $outInterest = '0.00';

            $outPrincipal = bcsub($payment->principal_component ?? '0.00', $payment->principal_paid ?? '0.00', 2);
            if (bccomp($outPrincipal, '0.00', 2) < 0) $outPrincipal = '0.00';

            $remainingDue = bcadd(bcadd($outPenalty, $outInterest, 2), $outPrincipal, 2);
            if (bccomp($remainingDue, '0.00', 2) <= 0) continue;

            $hasCredit = bccomp($loan->credit_balance ?? '0.00', '0.00', 2) > 0;
            $isDueOrOverdue = $runDate->gte($dueDate);
            $hasPaymentToday = isset($transactionsToday[$loan->id]);

            if (!($hasCredit && ($isDueOrOverdue || $hasPaymentToday))) {
                continue;
            }

            $applied = (bccomp($loan->credit_balance, $remainingDue, 2) >= 0) ? $remainingDue : $loan->credit_balance;

            $loan->credit_balance = bcsub($loan->credit_balance, $applied, 2);

            $paidOn = $isDueOrOverdue ? $dueDate : $runDate;

            $remaining = $applied;

            $deductPenalty = (bccomp($remaining, $outPenalty, 2) >= 0) ? $outPenalty : $remaining;
            $remaining     = bcsub($remaining, $deductPenalty, 2);
            $payment->penalty_paid = bcadd($payment->penalty_paid ?? '0.00', $deductPenalty, 2);

            $deductInterest = (bccomp($remaining, $outInterest, 2) >= 0) ? $outInterest : $remaining;
            $remaining      = bcsub($remaining, $deductInterest, 2);
            $payment->interest_paid = bcadd($payment->interest_paid ?? '0.00', $deductInterest, 2);

            $deductPrincipal = (bccomp($remaining, $outPrincipal, 2) >= 0) ? $outPrincipal : $remaining;
            $remaining       = bcsub($remaining, $deductPrincipal, 2);
            $payment->principal_paid = bcadd($payment->principal_paid ?? '0.00', $deductPrincipal, 2);

            $payment->amount_paid = bcadd($payment->amount_paid ?? '0.00', $applied, 2);

            $allPaid =
                bccomp($payment->penalty_paid   ?? '0.00', $payment->penalty_amount     ?? '0.00', 2) >= 0 &&
                bccomp($payment->interest_paid  ?? '0.00', $payment->interest_component ?? '0.00', 2) >= 0 &&
                bccomp($payment->principal_paid ?? '0.00', $payment->principal_component?? '0.00', 2) >= 0;

            if ($allPaid) {
                $completionDate = $runDate;

                if ($completionDate->lte($dueDate)) {
                    $payment->status = 'paid';
                } else {
                    $payment->status = 'paid_late';
                }

                $payment->paid_on = $completionDate;
            } else {
                $payment->status = 'partially_paid';
            }

            $payment->save();
            $loan->save();

            Transaction::create([
                'loan_id'        => $loan->id,
                'amount_paid'    => $applied,
                'penalty_paid'   => $deductPenalty,
                'interest_paid'  => $deductInterest,
                'principal_paid' => $deductPrincipal,
                'payment_date'   => $paidOn,
                'payment_method' => 'credit_balance',
                'notes'          => "EOD auto-applied credit to installment #{$payment->payment_number}",
            ]);

            $this->line("Applied $$applied to payment #{$payment->payment_number} for loan {$loan->loan_identifier} | Penalty: $deductPenalty, Interest: $deductInterest, Principal: $deductPrincipal | Remaining credit: {$loan->credit_balance}");

            $processedLoanIds[$loan->id] = true;
        }
    }

    protected function processPenalties(Carbon $nextDate)
    {
        $latePayments = RepaymentSchedule::with('loan.loanType')
            ->whereIn('status', ['pending', 'due', 'late', 'partially_paid'])
            ->where('payment_amount', '>', 0)
            ->whereDate('due_date', '<', $nextDate)
            ->get();

        if ($latePayments->isEmpty()) {
            Log::info('No late payments found.');
            return;
        }

        Log::info("Found {$latePayments->count()} payment(s) to process for penalties.");

        foreach ($latePayments as $payment) {
            $loanType  = $payment->loan->loanType;
            $graceDays = $loanType->grace_days ?? 0;

            $dueDate      = Carbon::parse($payment->due_date);
            $graceEndDate = $dueDate->copy()->addDays($graceDays);

            if ($payment->status === 'paid' || bccomp($payment->amount_paid ?? '0.00', $payment->payment_amount, 2) >= 0) {
                continue;
            }

            $lastPenaltyDate = $payment->last_penalty_date
                ? Carbon::parse($payment->last_penalty_date)
                : null;

            if ($nextDate->gt($dueDate) && $nextDate->lte($graceEndDate)) {
                if ($payment->status !== 'partially_paid') {
                    $payment->status = 'due';
                }
                $payment->save();
                continue;
            }

            if ($nextDate->gt($graceEndDate)) {
                $penaltyStartDate = $lastPenaltyDate ?? $dueDate;
                $newDays = $penaltyStartDate->diffInDays($nextDate);
                if ($newDays <= 0) continue;

                $remainingAmount = bcsub($payment->payment_amount, $payment->amount_paid ?? '0.00', 2);
                if (bccomp($remainingAmount, '0.00', 2) <= 0) continue;

                $dailyPenalty = '0.00';
                if ($loanType->penalty_type === 'flat_fee') {
                    $dailyPenalty = $loanType->penalty_amount;
                } elseif ($loanType->penalty_type === 'percentage') {
                    $dailyPenalty = bcmul($remainingAmount, bcdiv($loanType->penalty_amount, '100', 8), 2);
                }

                $penaltyToApply = bcmul($dailyPenalty, $newDays, 2);

                $payment->status            = 'late';
                $payment->penalty_amount    = bcadd($payment->penalty_amount ?? '0.00', $penaltyToApply, 2);
                $payment->last_penalty_date = $nextDate;
                $payment->save();

                $this->line(sprintf(
                    'Applied $%s penalty (%s days @ $%s/day) to payment #%s for loan %s (due %s, grace %d, remaining $%s).',
                    $penaltyToApply,
                    $newDays,
                    $dailyPenalty,
                    $payment->payment_number,
                    $payment->loan->loan_identifier,
                    $dueDate->toDateString(),
                    $graceDays,
                    $remainingAmount
                ));
            }
        }

        Log::info('Successfully processed all late payments.');
    }

    protected function advanceSystemDate(Setting $settings, Carbon $nextDate)
    {
        $settings->system_date = $nextDate;
        $settings->save();
        Log::info("System date has been advanced to: {$settings->system_date->toDateString()}");
    }
}