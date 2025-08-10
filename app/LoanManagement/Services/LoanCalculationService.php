<?php

namespace App\LoanManagement\Services;

use App\LoanManagement\Models\Loan;
use Carbon\Carbon;

class LoanCalculationService
{
    public function generateSchedule(Loan $loan)
    {
        // Clear any existing schedule to prevent duplicates
        $loan->repaymentSchedules()->delete();

        switch ($loan->LoanType->calculation_type) {
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

    protected function generateFlatInterestSchedule(Loan $loan)
    {
        // Using BCMath for precision (2 decimal places)
        $monthlyInterest = bcdiv(bcmul($loan->principal_amount, bcdiv($loan->interest_rate, 100, 4), 4), 12, 2);
        $monthlyPrincipal = bcdiv($loan->principal_amount, $loan->term, 2);
        $monthlyPayment = bcadd($monthlyPrincipal, $monthlyInterest, 2);
        $balance = $loan->principal_amount;

        for ($i = 1; $i <= $loan->term; $i++) {
            $balance = bcsub($balance, $monthlyPrincipal, 2);

            // Adjust the last payment to clear the balance exactly
            if ($i === $loan->term && $balance != 0) {
                $monthlyPrincipal = bcadd($monthlyPrincipal, $balance, 2);
                $monthlyPayment = bcadd($monthlyPrincipal, $monthlyInterest, 2);
                $balance = 0;
            }

            $loan->repaymentSchedules()->create([
                'payment_number' => $i,
                'due_date' => Carbon::parse($loan->approval_date)->addMonths($i),
                'payment_amount' => $monthlyPayment,
                'principal_component' => $monthlyPrincipal,
                'interest_component' => $monthlyInterest,
                'remaining_balance' => max(0, $balance),
            ]);
        }
    }

    protected function generateDecliningBalanceSchedule(Loan $loan)
    {
        $monthlyInterestRate = bcdiv(bcdiv($loan->interest_rate, 100, 10), 12, 10);
        $balance = $loan->principal_amount;

        // Standard amortization formula using BCMath
        $numerator = bcmul($balance, bcmul($monthlyInterestRate, bcpow(bcadd(1, $monthlyInterestRate, 10), $loan->term, 10), 10), 10);
        $denominator = bcsub(bcpow(bcadd(1, $monthlyInterestRate, 10), $loan->term, 10), 1, 10);
        $monthlyPayment = bcdiv($numerator, $denominator, 2);

        for ($i = 1; $i <= $loan->term; $i++) {
            $interestComponent = bcmul($balance, $monthlyInterestRate, 2);
            $principalComponent = bcsub($monthlyPayment, $interestComponent, 2);
            $balance = bcsub($balance, $principalComponent, 2);

            // Adjust the last payment to clear the balance exactly
            if ($i === $loan->term && $balance != 0) {
                $principalComponent = bcadd($principalComponent, $balance, 2);
                $monthlyPayment = bcadd($principalComponent, $interestComponent, 2);
                $balance = 0;
            }

            $loan->repaymentSchedules()->create([
                'payment_number' => $i,
                'due_date' => Carbon::parse($loan->approval_date)->addMonths($i),
                'payment_amount' => $monthlyPayment,
                'principal_component' => $principalComponent,
                'interest_component' => $interestComponent,
                'remaining_balance' => max(0, $balance),
            ]);
        }
    }

    protected function generateInterestOnlySchedule(Loan $loan)
    {
        $monthlyInterest = bcdiv(bcmul($loan->principal_amount, bcdiv($loan->interest_rate, 100, 4), 4), 12, 2);
        $balance = $loan->principal_amount;

        for ($i = 1; $i <= $loan->term; $i++) {
            $isLastPayment = ($i === $loan->term);
            $principalComponent = $isLastPayment ? $loan->principal_amount : '0.00';
            $paymentAmount = bcadd($monthlyInterest, $principalComponent, 2);

            if ($isLastPayment) {
                $balance = '0.00';
            }

            $loan->repaymentSchedules()->create([
                'payment_number' => $i,
                'due_date' => Carbon::parse($loan->approval_date)->addMonths($i),
                'payment_amount' => $paymentAmount,
                'principal_component' => $principalComponent,
                'interest_component' => $monthlyInterest,
                'remaining_balance' => $balance,
            ]);
        }
    }
}