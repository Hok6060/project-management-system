<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\RepaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request, RepaymentSchedule $schedule)
    {
        $validatedData = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create the transaction record
        $schedule->transactions()->create([
            'loan_id' => $schedule->loan_id,
            'user_id' => Auth::id(),
            'amount_paid' => $validatedData['amount_paid'],
            'payment_date' => $validatedData['payment_date'],
            'payment_method' => $validatedData['payment_method'],
            'notes' => $validatedData['notes'],
        ]);

        // Update the schedule's paid amount
        $newPaidAmount = bcadd($schedule->paid_amount, $validatedData['amount_paid'], 2);
        $schedule->paid_amount = $newPaidAmount;

        // Determine the new status
        $totalDue = bcadd($schedule->payment_amount, $schedule->penalty_amount, 2);
        if (bccomp($newPaidAmount, $totalDue, 2) >= 0) {
            // If fully paid, check if it was late
            $dueDate = Carbon::parse($schedule->due_date);
            $paymentDate = Carbon::parse($validatedData['payment_date']);
            $schedule->status = $paymentDate->isAfter($dueDate) ? 'paid_late' : 'paid';
            $schedule->paid_on = $validatedData['payment_date'];
        } else {
            $schedule->status = 'partially_paid';
        }

        $schedule->save();

        return back()->with('success', 'Payment has been recorded successfully.');
    }
}