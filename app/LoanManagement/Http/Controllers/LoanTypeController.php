<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\LoanType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LoanTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loanTypes = LoanType::latest()->paginate(10);
        return view('admin.loan-types.index', compact('loanTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.loan-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:loan_types'],
            'description' => ['nullable', 'string'],
            'calculation_type' => ['required', Rule::in(['flat_interest', 'declining_balance', 'interest_only'])],
            'min_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_interest_rate' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_interest_rate'],
            'min_term' => ['required', 'integer', 'min:1'],
            'max_term' => ['required', 'integer', 'gte:min_term'],
            'is_active' => ['sometimes', 'boolean'],
            'penalty_type' => ['required', Rule::in(['flat_fee', 'percentage'])],
            'penalty_amount' => ['required', 'numeric', 'min:0'],
            'prepayment_penalty_period' => ['nullable', 'integer', 'min:0'],
            'prepayment_penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validatedData['is_active'] = $request->has('is_active');

        LoanType::create($validatedData);

        return redirect()->route('admin.loan-types.index')->with('success', 'Loan Type created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoanType $loanType)
    {
        return view('admin.loan-types.edit', compact('loanType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoanType $loanType)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('loan_types')->ignore($loanType->id)],
            'description' => ['nullable', 'string'],
            'calculation_type' => ['required', Rule::in(['flat_interest', 'declining_balance', 'interest_only'])],
            'min_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_interest_rate' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_interest_rate'],
            'min_term' => ['required', 'integer', 'min:1'],
            'max_term' => ['required', 'integer', 'gte:min_term'],
            'is_active' => ['sometimes', 'boolean'],
            'penalty_type' => ['required', Rule::in(['flat_fee', 'percentage'])],
            'penalty_amount' => ['required', 'numeric', 'min:0'],
            'prepayment_penalty_period' => ['nullable', 'integer', 'min:0'],
            'prepayment_penalty_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validatedData['is_active'] = $request->has('is_active');

        $loanType->update($validatedData);

        return redirect()->route('admin.loan-types.index')->with('success', 'Loan Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoanType $loanType)
    {
        // Add logic here to check if the type is in use before deleting
        $loanType->delete();
        return redirect()->route('admin.loan-types.index')->with('success', 'Loan Type deleted successfully.');
    }
}