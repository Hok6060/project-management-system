<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Models\RepaymentSchedule;
use App\LoanManagement\Models\Transaction;
use Carbon\Carbon;

class ProcessEOD extends Command
{
    protected $signature = 'app:process-eod';

    protected $description = 'Processes End-of-Day tasks and advances the system date on success.';

    public function handle()
    {
        $this->info('Starting End-of-Day process...');

        $settings = Setting::first();
        if (!$settings) {
            $this->error('System settings not found. Aborting.');
            return 1;
        }

        $runDate  = Carbon::parse($settings->system_date)->startOfDay();
        $nextDate = $runDate->copy()->addDay()->startOfDay();
        $this->info("Processing EOD for {$runDate->toDateString()} -> {$nextDate->toDateString()}");

        $this->autoPayZeroInstallments($nextDate);
        $this->applyCreditBalance($runDate, $nextDate);
        $this->processPenalties($nextDate);
        $this->advanceSystemDate($settings, $nextDate);

        $this->info('EOD process complete.');
        return 0;
    }

    /**
     * Auto mark $0 installments as paid
     */
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
                $payment->due_date->toDateString()
            ));
        }
    }

    /**
     * Apply available credit balance toward installments.
     * Deduction order: penalty → interest → principal
     */
    protected function applyCreditBalance(Carbon $runDate, Carbon $nextDate)
    {
        $pendingPayments = RepaymentSchedule::with('loan')
            ->whereIn('status', ['pending', 'due', 'late', 'partially_paid'])
            ->where('payment_amount', '>', 0)
            ->orderBy('due_date')
            ->get();

        // Get today's transactions (customer payments)
        $transactionsToday = Transaction::whereDate('payment_date', $runDate->toDateString())
            ->get()
            ->groupBy('loan_id');

        foreach ($pendingPayments as $payment) {
            $loan    = $payment->loan;
            $dueDate = Carbon::parse($payment->due_date);

            // How much total is still due for the installment
            $remainingDue = bcsub($payment->payment_amount, $payment->amount_paid ?? '0.00', 2);
            if (bccomp($remainingDue, '0.00', 2) <= 0) continue;

            $applied = '0.00';
            $paidOn  = null;
            $deductPenalty = '0.00';
            $deductInterest = '0.00';
            $deductPrincipal = '0.00';

            // Case 1: Due today or overdue → always deduct from credit
            if ($runDate->gte($dueDate) && bccomp($loan->credit_balance, '0.00', 2) > 0) {
                $applied = min($loan->credit_balance, $remainingDue);
                $loan->credit_balance = bcsub($loan->credit_balance, $applied, 2);
                $paidOn = $dueDate;
            }
            // Case 2: Early payment → only if customer paid today
            elseif ($runDate->lt($dueDate) && isset($transactionsToday[$loan->id])) {
                $applied = min($loan->credit_balance, $remainingDue);
                $loan->credit_balance = bcsub($loan->credit_balance, $applied, 2);
                $paidOn = $runDate;
            }

            if (bccomp($applied, '0.00', 2) > 0) {
            $remaining = $applied;

            // --- Deduct Penalty ---
            $remainingPenalty  = bcsub($payment->penalty_amount ?? '0.00', $payment->penalty_paid ?? '0.00', 2);
            if (bccomp($remainingPenalty, '0.00', 2) > 0) {
                $deductPenalty = min($remaining, $remainingPenalty);
                $remaining     = bcsub($remaining, $deductPenalty, 2);
                $payment->penalty_paid = bcadd($payment->penalty_paid ?? '0.00', $deductPenalty, 2);
            }

            // --- Deduct Interest ---
            $remainingInterest = bcsub($payment->interest_component ?? '0.00', $payment->interest_paid ?? '0.00', 2);
            if (bccomp($remainingInterest, '0.00', 2) > 0) {
                $deductInterest    = min($remaining, $remainingInterest);
                $remaining         = bcsub($remaining, $deductInterest, 2);
                $payment->interest_paid = bcadd($payment->interest_paid ?? '0.00', $deductInterest, 2);
            }

            // --- Deduct Principal ---
            $remainingPrincipal = bcsub($payment->principal_component ?? '0.00', $payment->principal_paid ?? '0.00', 2);
            if (bccomp($remainingPrincipal, '0.00', 2) > 0) {
                $deductPrincipal    = min($remaining, $remainingPrincipal);
                $remaining          = bcsub($remaining, $deductPrincipal, 2);
                $payment->principal_paid = bcadd($payment->principal_paid ?? '0.00', $deductPrincipal, 2);
            }

            // --- Update total applied & status ---
            $payment->amount_paid = bcadd($payment->amount_paid ?? '0.00', $applied, 2);

            if (
                bccomp($payment->penalty_paid ?? '0.00', $payment->penalty_amount ?? '0.00', 2) >= 0 &&
                bccomp($payment->interest_paid ?? '0.00', $payment->interest_component ?? '0.00', 2) >= 0 &&
                bccomp($payment->principal_paid ?? '0.00', $payment->principal_component ?? '0.00', 2) >= 0
            ) {
                $payment->status = ($paidOn && $paidOn <= $dueDate) ? 'paid' : 'paid_late';
                $payment->paid_on = $paidOn;
            } else {
                $payment->status = 'partially_paid';
            }

            $payment->save();
            $loan->save();

            // --- Transaction log ---
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

            $this->line("Applied $$applied to payment #{$payment->payment_number} for loan {$loan->loan_identifier} | Penalty: $deductPenalty, Interest: $deductInterest, Principal: $deductPrincipal | Remaining credit: $loan->credit_balance");
            }
        }
    }

    /**
     * Add penalties to overdue installments
     */
    protected function processPenalties(Carbon $nextDate)
    {
        $latePayments = RepaymentSchedule::with('loan.loanType')
            ->whereIn('status', ['pending', 'due', 'late', 'partially_paid'])
            ->where('payment_amount', '>', 0)
            ->whereDate('due_date', '<', $nextDate)
            ->get();

        if ($latePayments->isEmpty()) {
            $this->info('No late payments found.');
            return;
        }

        $this->info("Found {$latePayments->count()} payment(s) to process for penalties.");

        foreach ($latePayments as $payment) {
            $loanType  = $payment->loan->loanType;
            $graceDays = $loanType->grace_days ?? 0;

            $dueDate      = Carbon::parse($payment->due_date);
            $graceEndDate = $dueDate->copy()->addDays($graceDays);

            // Skip fully paid
            if ($payment->status === 'paid' || bccomp($payment->amount_paid ?? '0.00', $payment->payment_amount, 2) >= 0) {
                continue;
            }

            $lastPenaltyDate = $payment->last_penalty_date
                ? Carbon::parse($payment->last_penalty_date)
                : null;

            if ($nextDate->greaterThan($graceEndDate)) {
                $penaltyStartDate = $lastPenaltyDate ?? $dueDate;
            } else {
                continue;
            }

            $newDays = $penaltyStartDate->diffInDays($nextDate);
            if ($newDays <= 0) continue;

            $remainingAmount = bcsub($payment->payment_amount, $payment->amount_paid ?? '0.00', 2);
            if (bccomp($remainingAmount, '0.00', 2) <= 0) continue;

            // Calculate daily penalty
            $dailyPenalty = '0.00';
            if ($loanType->penalty_type === 'flat_fee') {
                $dailyPenalty = $loanType->penalty_amount;
            } elseif ($loanType->penalty_type === 'percentage') {
                $dailyPenalty = bcmul($remainingAmount, bcdiv($loanType->penalty_amount, '100', 8), 2);
            }

            // Total penalty to apply
            $penaltyToApply = bcmul($dailyPenalty, $newDays, 2);

            // Accumulate penalty (DO NOT clear old)
            $payment->status           = 'late';
            $payment->penalty_amount   = bcadd($payment->penalty_amount ?? '0.00', $penaltyToApply, 2);
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

        $this->info('Successfully processed all late payments.');
    }

    /**
     * Move system date forward
     */
    protected function advanceSystemDate(Setting $settings, Carbon $nextDate)
    {
        $settings->system_date = $nextDate;
        $settings->save();
        $this->info("System date has been advanced to: {$settings->system_date->toDateString()}");
    }
}