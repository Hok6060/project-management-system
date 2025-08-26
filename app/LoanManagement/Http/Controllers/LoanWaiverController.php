<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\LoanManagement\Services\LoanCalculationService;

class LoanWaiverController extends Controller
{
    /**
     * Display a listing of the waiver requests for admin approval.
     */
    public function index()
    {
        $pendingWaivers = \App\LoanManagement\Models\LoanWaiver::with('loan.customer', 'requester')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('loan-management.admin.waivers.index', compact('pendingWaivers'));
    }

    /**
     * Update the specified waiver request in storage (Approve/Reject).
     */
    public function update(Request $request, \App\LoanManagement\Models\LoanWaiver $waiver, LoanCalculationService $loanCalculator)
    {
        $validatedData = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
        ]);

        DB::transaction(function () use ($waiver, $validatedData, $loanCalculator) {
            $waiver->status = $validatedData['status'];
            $waiver->approved_by_user_id = Auth::id();
            $waiver->processed_at = now();
            $waiver->save();

            // If approved, apply the waiver to the loan
            if ($waiver->status === 'approved') {
                $loan = $waiver->loan;
                $amountToWaive = $waiver->amount;

                if ($waiver->waiver_type === 'principal') {
                    // Waiving principal is a special case. We reduce the loan's principal amount
                    // and then completely regenerate the future schedule.
                    $loan->principal_amount = bcsub($loan->principal_amount, $amountToWaive, 2);
                    $loan->save();
                    $loanCalculator->generateSchedule($loan); // Recalculate!

                } else {
                    // For penalty or interest, we apply the waiver to the oldest unpaid installments first.
                    $unpaidSchedules = $loan->repaymentSchedules()
                        ->whereIn('status', ['pending', 'due', 'late', 'partially_paid'])
                        ->orderBy('due_date', 'asc')
                        ->get();

                    foreach ($unpaidSchedules as $schedule) {
                        if (bccomp($amountToWaive, '0.01', 2) < 0) break;

                        $fieldToWaive = ($waiver->waiver_type === 'late_penalty') ? 'penalty_paid' : 'interest_paid';
                        $componentField = ($waiver->waiver_type === 'late_penalty') ? 'penalty_amount' : 'interest_component';

                        $amountOwed = bcsub($schedule->{$componentField}, $schedule->{$fieldToWaive}, 2);
                        
                        if ($amountOwed > 0) {
                            $waiverForThisInstallment = bccomp($amountToWaive, $amountOwed, 2) >= 0 ? $amountOwed : $amountToWaive;
                            
                            $schedule->{$fieldToWaive} = bcadd($schedule->{$fieldToWaive}, $waiverForThisInstallment, 2);
                            $amountToWaive = bcsub($amountToWaive, $waiverForThisInstallment, 2);
                            $schedule->save();
                        }
                    }
                }
            }
        });

        return redirect()->route('admin.waivers.index')->with('success', 'Waiver request has been processed.');
    }
    
    /**
     * Show the form for creating a new waiver request.
     */
    public function create(Loan $loan)
    {
        return view('loan-management.admin.waivers.create', compact('loan'));
    }

    /**
     * Store a newly created waiver request in storage.
     */
    public function store(Request $request, Loan $loan)
    {
        $validatedData = $request->validate([
            'waiver_type' => ['required', Rule::in(['late_penalty', 'interest', 'principal'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $maxWaiverAmount = '0.00';
        $waiverType = $validatedData['waiver_type'];
        $amountToWaive = $validatedData['amount'];

        if ($waiverType === 'late_penalty') {
            $maxWaiverAmount = $loan->repaymentSchedules->sum(function ($schedule) {
                return bcsub($schedule->penalty_amount ?? '0.00', $schedule->penalty_paid ?? '0.00', 2);
            });
        } elseif ($waiverType === 'interest') {
            $maxWaiverAmount = $loan->repaymentSchedules->sum(function ($schedule) {
                return bcsub($schedule->interest_component ?? '0.00', $schedule->interest_paid ?? '0.00', 2);
            });
        } elseif ($waiverType === 'principal') {
            $totalPrincipalPaid = $loan->repaymentSchedules->sum('principal_paid');
            $maxWaiverAmount = bcsub($loan->principal_amount, $totalPrincipalPaid, 2);
        }

        if (bccomp($amountToWaive, $maxWaiverAmount, 2) > 0) {
            return back()->withInput()->with('error', "The maximum waiver amount for this " . ucwords(str_replace('_', ' ', $waiverType)) . " is \${$maxWaiverAmount}.");
        }

        $loan->waivers()->create([
            'requested_by_user_id' => Auth::id(),
            'waiver_type' => $validatedData['waiver_type'],
            'amount' => $validatedData['amount'],
            'reason' => $validatedData['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('loans.admin.show', $loan)->with('success', 'Waiver request has been submitted for approval.');
    }

    public function calculateMaxWaiver(Request $request, Loan $loan)
    {
        $waiverType = $request->query('type');
        $maxWaiverAmount = '0.00';

        if ($waiverType === 'late_penalty') {
            $maxWaiverAmount = $loan->repaymentSchedules->sum(function ($schedule) {
                return bcsub($schedule->penalty_amount ?? '0.00', $schedule->penalty_paid ?? '0.00', 2);
            });
        } elseif ($waiverType === 'interest') {
            $maxWaiverAmount = $loan->repaymentSchedules->sum(function ($schedule) {
                return bcsub($schedule->interest_component ?? '0.00', $schedule->interest_paid ?? '0.00', 2);
            });
        } elseif ($waiverType === 'principal') {
            $totalPrincipalPaid = $loan->repaymentSchedules->sum('principal_paid');
            $maxWaiverAmount = bcsub($loan->principal_amount, $totalPrincipalPaid, 2);
        }

        return response()->json(['max_waiver_amount' => $maxWaiverAmount]);
    }
}