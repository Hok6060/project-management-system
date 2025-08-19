<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Loan;
use App\LoanManagement\Models\RepaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request, Loan $loan)
    {
        $validatedData = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($loan, $validatedData) {
            $loan->transactions()->create([
                'amount_paid' => $validatedData['amount_paid'],
                'payment_date' => $validatedData['payment_date'],
                'payment_method' => $validatedData['payment_method'],
                'notes' => $validatedData['notes'],
            ]);

            $loan->credit_balance = bcadd($loan->credit_balance, $validatedData['amount_paid'], 2);
            $loan->save();
        });

        return back()->with('success', 'Payment recorded successfully.');
    }
}