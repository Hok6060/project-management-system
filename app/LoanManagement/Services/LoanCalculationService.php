<?php

namespace App\LoanManagement\Services;

use App\LoanManagement\Models\Loan;
use Carbon\Carbon;

class LoanCalculationService
{
    public function generateSchedule(Loan $loan)
    {
        $loan->repaymentSchedules()->delete();

        switch ($loan->loanType->calculation_type) {
            case 'flat_interest':
                $this->generateFlatInterestSchedule($loan);
                break;

            case 'declining_balance':
                $this->generateDecliningBalanceSchedule($loan);
                break;

            case 'interest_only':
                $this->generateInterestOnlySchedule($loan);
                break;
        }
    }

    protected function generateDecliningBalanceSchedule(Loan $loan)
    {
        $termMonths        = (int) $loan->term;
        $monthsPerPayment  = $this->getMonthsPerPayment($loan->payment_frequency);
        $principalPeriods  = (int) max(1, floor($termMonths / $monthsPerPayment));
        $balance           = $this->toStr($loan->principal_amount);
        $annualRate        = $this->toStr($loan->interest_rate);
        $firstDue          = Carbon::parse($loan->first_payment_date)->startOfDay();
        $prevDue           = $firstDue->copy()->subMonth();

        $equalPrincipal    = $principalPeriods > 0
            ? $this->money(bcdiv($balance, (string) $principalPeriods, 10))
            : '0.00';

        $principalPaysMade = 0;

        for ($i = 1; $i <= $termMonths; $i++) {
            $dueDate = $firstDue->copy()->addMonthsNoOverflow($i - 1);
            $days    = $prevDue->diffInDays($dueDate);

            if ($i <= (int) $loan->interest_free_periods) {
                $interestComponent = '0.00';
            } else {
                $interestComponent = $this->accrueInterestActual365($balance, $annualRate, $days);
            }

            $principalComponent = '0.00';
            $isPrincipalMonth   = ($monthsPerPayment > 0) && ($i % $monthsPerPayment === 0);

            if ($isPrincipalMonth) {
                $principalPaysMade++;
                $isLastPrincipalPeriod = ($principalPaysMade === $principalPeriods) || ($i === $termMonths);

                if ($isLastPrincipalPeriod) {
                    $principalComponent = $balance;
                } else {
                    $principalComponent = $this->minMoney($equalPrincipal, $balance);
                }
            }

            $paymentAmount = $this->money(bcadd($interestComponent, $principalComponent, 10));

            $balance = $this->money(bcsub($balance, $principalComponent, 10));
            if (bccomp($balance, '0', 10) < 0) {
                $balance = '0.00';
            }

            $loan->repaymentSchedules()->create([
                'payment_number'       => $i,
                'due_date'             => $dueDate,
                'payment_amount'       => $paymentAmount,
                'principal_component'  => $principalComponent,
                'interest_component'   => $interestComponent,
                'remaining_balance'    => $balance,
            ]);

            $prevDue = $dueDate;
        }
    }

    protected function generateFlatInterestSchedule(Loan $loan)
    {
        $termMonths       = (int) $loan->term;
        $monthsPerPayment = $this->getMonthsPerPayment($loan->payment_frequency);
        $principalPeriods = max(1, (int) floor($termMonths / $monthsPerPayment));
        $balance          = $this->toStr($loan->principal_amount);
        $principalBase    = $this->toStr($loan->principal_amount);
        $annualRate       = $this->toStr($loan->interest_rate);
        $firstDue         = Carbon::parse($loan->first_payment_date)->startOfDay();
        $prevDue          = $firstDue->copy()->subMonth();

        $equalPrincipal = $principalPeriods > 0
            ? $this->money(bcdiv($balance, (string) $principalPeriods, 10))
            : '0.00';

        $principalPaysMade = 0;

        for ($i = 1; $i <= $termMonths; $i++) {
            $dueDate = $firstDue->copy()->addMonthsNoOverflow($i - 1);
            $days    = $prevDue->diffInDays($dueDate);

            if ($i <= (int) $loan->interest_free_periods) {
                $interestComponent = '0.00';
            } else {
                $interestComponent = $this->accrueInterestActual365($principalBase, $annualRate, $days);
            }

            $principalComponent = '0.00';
            if ($monthsPerPayment > 0 && $i % $monthsPerPayment === 0) {
                $principalPaysMade++;
                $isLastPrincipalPeriod = ($principalPaysMade === $principalPeriods) || ($i === $termMonths);

                $principalComponent = $isLastPrincipalPeriod
                    ? $balance
                    : $this->minMoney($equalPrincipal, $balance);
            }

            $paymentAmount = $this->money(bcadd($interestComponent, $principalComponent, 10));

            $balance = $this->money(bcsub($balance, $principalComponent, 10));
            if (bccomp($balance, '0', 10) < 0) {
                $balance = '0.00';
            }

            $loan->repaymentSchedules()->create([
                'payment_number'      => $i,
                'due_date'            => $dueDate,
                'payment_amount'      => $paymentAmount,
                'principal_component' => $principalComponent,
                'interest_component'  => $interestComponent,
                'remaining_balance'   => $balance,
            ]);

            $prevDue = $dueDate;
        }
    }

    protected function generateInterestOnlySchedule(Loan $loan)
    {
        $termMonths    = (int) $loan->term;
        $balance       = $this->toStr($loan->principal_amount);
        $principalBase = $this->toStr($loan->principal_amount);
        $annualRate    = $this->toStr($loan->interest_rate);
        $firstDue      = Carbon::parse($loan->first_payment_date)->startOfDay();
        $prevDue       = $firstDue->copy()->subMonth();

        for ($i = 1; $i <= $termMonths; $i++) {
            $dueDate = $firstDue->copy()->addMonthsNoOverflow($i - 1);
            $days    = $prevDue->diffInDays($dueDate);

            if ($i <= (int) $loan->interest_free_periods) {
                $interestComponent = '0.00';
            } else {
                $interestComponent = $this->accrueInterestActual365($principalBase, $annualRate, $days);
            }

            $isLast = ($i === $termMonths);
            $principalComponent = $isLast ? $balance : '0.00';

            $paymentAmount = $this->money(bcadd($interestComponent, $principalComponent, 10));

            $balance = $this->money(bcsub($balance, $principalComponent, 10));
            if (bccomp($balance, '0', 10) < 0) {
                $balance = '0.00';
            }

            $loan->repaymentSchedules()->create([
                'payment_number'       => $i,
                'due_date'             => $dueDate,
                'payment_amount'       => $paymentAmount,
                'principal_component'  => $principalComponent,
                'interest_component'   => $interestComponent,
                'remaining_balance'    => $balance,
            ]);

            $prevDue = $dueDate;
        }
    }

    private function getMonthsPerPayment(string $frequency): int
    {
        return match ($frequency) {
            'quarterly'      => 3,
            'semi_annually'  => 6,
            'monthly'        => 1,
            default          => 1,
        };
    }

    private function accrueInterestActual365(string $base, string $annualRate, int $days): string
    {
        if ($days <= 0) return '0.00';
        $dailyRate = bcdiv(bcdiv($annualRate, '100', 10), '365', 10);
        $daysStr   = (string) $days;

        $interest  = bcmul(bcmul($base, $dailyRate, 10), $daysStr, 10);
        return $this->money($interest);
    }

    private function minMoney(string $a, string $b): string
    {
        return (bccomp($a, $b, 10) <= 0) ? $this->money($a) : $this->money($b);
    }

    private function money(string $n): string
    {
        return number_format((float) $n, 2, '.', '');
    }

    private function toStr($n): string
    {
        return is_string($n) ? $n : (string) $n;
    }
}