<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\LoanManagement\Models\Setting;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'system_date' => Carbon::today(),
            'eod_mode' => 'auto',
        ]);
    }
}