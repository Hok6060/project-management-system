<?php

namespace App\LoanManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\LoanManagement\Models\Setting;
use App\LoanManagement\Jobs\ProcessEodJob;
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

    /**
     * Manually trigger the End-of-Day process and advance the date on success.
     */
    public function runEod()
    {
        ProcessEodJob::dispatch();

        return redirect()->route('admin.settings.eodProgress')->with('success', 'The End-of-Day process has been started in the background.');
    }

    /**
     * Display the EOD progress page.
     */
    public function eodProgress()
    {
        $logPath = storage_path('logs/laravel.log');
        $logContent = '';

        if (File::exists($logPath)) {
            $lines = file($logPath);
            $logContent = implode('', array_slice($lines, -50));
        }

        return view('loan-management.admin.settings.eod-progress', compact('logContent'));
    }
}