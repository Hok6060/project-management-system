<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SettingController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        // We know there is only one settings row, so we get the first one.
        $settings = Setting::first();
        return view('loan-management.admin.settings.index', compact('settings'));
    }

    /**
     * Update the system settings.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'system_date' => ['required', 'date'],
            'eod_mode' => ['required', Rule::in(['auto', 'manual'])],
        ]);

        $settings = Setting::first();
        if ($settings) {
            $settings->update($validatedData);
        }

        return redirect()->route('admin.settings.index')->with('success', 'System settings have been updated.');
    }

    /**
     * Manually trigger the End-of-Day process and advance the date on success.
     */
    public function runEod()
    {
        Artisan::call('app:process-eod');
        $output = Artisan::output();

        return back()->with('success', 'End-of-Day process has been run.')->with('eod_output', $output);
    }
}