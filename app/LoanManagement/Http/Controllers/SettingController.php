<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Jobs\ProcessEodJob;
use App\LoanManagement\Models\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

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

    public function runEod()
    {
        $jobStatus = JobStatus::create([
            'name' => 'End-of-Day Process',
            'status' => 'queued',
        ]);

        ProcessEodJob::dispatch($jobStatus);

        return redirect()->route('admin.settings.eodProgress', ['jobStatus' => $jobStatus]);
    }

    public function eodProgress(JobStatus $jobStatus)
    {
        return view('loan-management.admin.settings.eod-progress', compact('jobStatus'));
    }

    public function getEodStatus(JobStatus $jobStatus)
    {
        return response()->json($jobStatus);
    }
}