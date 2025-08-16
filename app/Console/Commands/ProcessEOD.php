<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Models\RepaymentSchedule;
use Carbon\Carbon;

class ProcessEOD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-eod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes End-of-Day tasks and advances the system date on success.';

    /**
     * Execute the console command.
     */
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

        $latePayments = RepaymentSchedule::with('loan.loanType')
            ->whereIn('status', ['pending', 'late'])
            ->where('payment_amount', '>', 0)
            ->whereDate('due_date', '<', $nextDate)
            ->get();

        if ($latePayments->isEmpty()) {
            $this->info('No late payments found.');
        } else {
            $this->info("Found {$latePayments->count()} payment(s) to process for penalties.");

            foreach ($latePayments as $payment) {
                $loanType = $payment->loan->loanType;
                $graceDays = $loanType->grace_days ?? 0;

                $dueDate = Carbon::parse($payment->due_date);
                $graceEndDate = $dueDate->copy()->addDays($graceDays);

                if ($payment->status === 'paid') {
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
                if ($newDays <= 0) {
                    continue;
                }

                $dailyPenalty = '0.00';
                if ($loanType->penalty_type === 'flat_fee') {
                    $dailyPenalty = $loanType->penalty_amount;
                } elseif ($loanType->penalty_type === 'percentage') {
                    $dailyPenalty = bcmul(
                        $payment->payment_amount,
                        bcdiv($loanType->penalty_amount, '100', 8),
                        2
                    );
                }

                $penaltyToApply = bcmul($dailyPenalty, $newDays, 2);

                $payment->status = 'late';
                $payment->penalty_amount = bcadd($payment->penalty_amount ?? '0.00', $penaltyToApply, 2);
                $payment->last_penalty_date = $nextDate;
                $payment->save();

                if ($newDays > 0) {
                    $this->line(sprintf(
                        'Applied $%s penalty (%s days @ $%s/day) to payment #%s for loan %s (due %s, grace %d).',
                        $penaltyToApply,
                        $newDays,
                        $dailyPenalty,
                        $payment->payment_number,
                        $payment->loan->loan_identifier,
                        $dueDate->toDateString(),
                        $graceDays
                    ));
                } else {
                    $this->line(sprintf(
                        'Skipped penalty for payment #%s of loan %s (due %s) — still in grace period.',
                        $payment->payment_number,
                        $payment->loan->loan_identifier,
                        $dueDate->toDateString()
                    ));
                }
            }

            $this->info('Successfully processed all late payments.');
        }

        $settings->system_date = $nextDate;
        $settings->save();
        $this->info("System date has been advanced to: {$settings->system_date->toDateString()}");

        $this->info('EOD process complete.');
        return 0;
    }
}