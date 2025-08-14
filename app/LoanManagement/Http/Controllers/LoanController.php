<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Services\LoanCalculationService; 
use App\LoanManagement\Models\Loan;
use App\LoanManagement\Models\LoanType;
use App\LoanManagement\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loans = Loan::with(['customer', 'loanType'])
                    ->latest('loan_identifier')
                    ->paginate(10);

        return view('loan-management.admin.index', compact('loans'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        // Eager load all the relationships we need
        $loan->load(['customer', 'loanType', 'loanOfficer', 'repaymentSchedules', 'activities.user']); // <-- Add 'activities.user'

        // Paginate the repayment schedules separately
        $schedules = $loan->repaymentSchedules()->paginate(12);

        $loanOfficers = User::whereIn('role', ['admin', 'loan_officer'])->orderBy('name')->get();

        return view('loan-management.admin.show', compact('loan', 'schedules', 'loanOfficers'));
    }
    
    /**
     * Show the form for creating a new loan application.
     */
    public function create()
    {
        // Fetch active loan types AND all customers
        $loanTypes = LoanType::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::orderBy('first_name')->get();
        $loanOfficers = User::whereIn('role', ['admin', 'loan_officer'])->orderBy('name')->get();

        return view('loan-management.apply', compact('loanTypes', 'customers', 'loanOfficers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        // Prevent editing if the loan is not pending
        if ($loan->status !== 'pending') {
            return redirect()->route('loans.admin.show', $loan)->with('error', 'Only pending loans can be edited.');
        }

        $loanTypes = LoanType::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::orderBy('first_name')->get();
        $loanOfficers = User::whereIn('role', ['admin', 'loan_officer'])->orderBy('name')->get();

        return view('loan-management.admin.edit', compact('loan', 'loanTypes', 'customers', 'loanOfficers'));
    }

    /**
     * Update the specified resource in storage. (Approve/Reject)
     */
    public function update(Request $request, Loan $loan, LoanCalculationService $loanCalculator)
    {
        // Prevent action if the loan is not pending
        if ($loan->status !== 'pending') {
            return back()->with('error', 'This loan has already been processed.');
        }

        $validatedData = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'details' => ['nullable', 'string', 'max:1000'], // For the comment
        ]);

        if (!$loan->loan_officer_id) {
            $loan->loan_officer_id = Auth::id();
        }

        $loan->status = $validatedData['status'];

        if ($validatedData['status'] === 'approved') {
            $loan->approval_date = now();
            $loanCalculator->generateSchedule($loan);
        }

        $loan->save();

        // Create an activity log entry
        $loan->activities()->create([
            'user_id' => Auth::id(),
            'description' => "{$validatedData['status']} the loan application",
            'details' => $validatedData['details'] ?? ($validatedData['status'] === 'approved' ? 'Approved' : null),
        ]);

        return redirect()->route('loans.admin.show', $loan)->with('success', 'Loan application has been updated.');
    }

    /**
     * Store a newly created loan application in storage.
     */
    public function store(Request $request)
    {
        $preValidation = $request->validate([
            'loan_type_id' => ['required', 'exists:loan_types,id'],
        ]);

        $loanType = LoanType::find($preValidation['loan_type_id']);

        $validatedData = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'loan_type_id' => ['required', 'exists:loan_types,id'],
            'principal_amount' => ['required', 'numeric', 'min:1'],
            'interest_rate' => ['required', 'numeric', 'min:'.$loanType->min_interest_rate, 'max:'.$loanType->max_interest_rate],
            'term' => ['required', 'integer', 'min:'.$loanType->min_term, 'max:'.$loanType->max_term],
            'interest_free_periods' => ['nullable', 'integer', 'min:0', 'lte:term'],
            'first_payment_date' => ['required', 'date', 'after:today'],
            'payment_frequency' => ['required', Rule::in(['monthly', 'quarterly', 'semi_annually'])],
            'loan_officer_id' => ['nullable', 'exists:users,id'],
        ]);

        Loan::create([
            'loan_identifier' => 'L' . str_pad(Loan::count() + 1, 5, '0', STR_PAD_LEFT),
            'loan_type_id' => $validatedData['loan_type_id'],
            'customer_id' => $validatedData['customer_id'],
            'loan_officer_id' => $validatedData['loan_officer_id'],
            'principal_amount' => $validatedData['principal_amount'],
            'interest_rate' => $validatedData['interest_rate'],
            'term' => $validatedData['term'],
            'interest_free_periods' => $validatedData['interest_free_periods'] ?? 0,
            'payment_frequency' => $validatedData['payment_frequency'],
            'status' => 'pending',
            'application_date' => now(),
            'first_payment_date' => $validatedData['first_payment_date'],
        ]);

        return redirect()->route('loans.admin.index')->with('success', 'Loan application submitted successfully!');
    }

    /**
     * Assign a loan officer to the specified loan.
     */
    public function assignOfficer(Request $request, Loan $loan)
    {
        $validatedData = $request->validate([
            'loan_officer_id' => ['required', 'exists:users,id'],
        ]);

        $loan->update($validatedData);

        return redirect()->route('loans.admin.index', $loan)->with('success', 'Loan Officer has been assigned.');
    }

    /**
     * Cancel a pending loan application.
     */
    public function cancel(Request $request, Loan $loan)
    {
        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be cancelled.');
        }

        $validatedData = $request->validate([
            'details' => ['required', 'string', 'max:1000'], // Make comment required for cancellation
        ]);

        $loan->status = 'cancelled';
        $loan->save();

        // Create an activity log entry
        $loan->activities()->create([
            'user_id' => Auth::id(),
            'description' => 'cancelled the loan application',
            'details' => $validatedData['details'],
        ]);

        return redirect()->route('loans.admin.show', $loan)->with('success', 'Loan application has been cancelled.');
    }
}