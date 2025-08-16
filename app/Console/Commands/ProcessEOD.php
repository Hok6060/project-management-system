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
            ->where('status', 'pending')
            ->where('payment_amount', '>', 0)
            ->whereDate('due_date', '<', $nextDate)
            ->get();

        if ($latePayments->isEmpty()) {
            $this->info('No late payments found.');
        } else {
            $this->info("Found {$latePayments->count()} late payment(s) to process.");
            foreach ($latePayments as $payment) {
                $loanType = $payment->loan->loanType;
                $penalty = '0.00';

                if ($loanType->penalty_type === 'flat_fee') {
                    $penalty = $loanType->penalty_amount;
                } elseif ($loanType->penalty_type === 'percentage') {
                    $penalty = bcmul($payment->payment_amount, bcdiv($loanType->penalty_amount, '100', 8), 2);
                }

                $payment->status = 'late';
                $payment->penalty_amount = bcadd($payment->penalty_amount ?? '0.00', $penalty, 2);
                $payment->save();

                $this->line(sprintf(
                    'Applied a penalty of $%s to payment #%s for loan %s (due %s).',
                    $penalty,
                    $payment->payment_number,
                    $payment->loan->loan_identifier,
                    Carbon::parse($payment->due_date)->toDateString()
                ));
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