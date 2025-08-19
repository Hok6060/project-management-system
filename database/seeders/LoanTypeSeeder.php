<?php

namespace Database\Seeders;

use App\LoanManagement\Models\LoanType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LoanTypeSeeder extends Seeder
{
    public function run(): void
    {
        LoanType::create([
            'name' => 'Agriculture Loan',
            'description' => 'A loan for agriculture purposes',
            'calculation_type' => 'declining_balance',
            'min_interest_rate' => 10.0,
            'max_interest_rate' => 30.0,
            'min_term' => 12,
            'max_term' => 60,
            'is_active' => true,
            'penalty_type' => 'flat_fee',
            'penalty_amount' => 5.0,
            'prepayment_penalty_period' => 12,
            'prepayment_penalty_amount' => 1500.0,
            'grace_days' => 2,
        ]);
    }
}