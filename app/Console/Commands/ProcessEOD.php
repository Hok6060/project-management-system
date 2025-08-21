<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LoanManagement\Jobs\ProcessEodJob;

class ProcessEOD extends Command
{
    protected $signature = 'app:process-eod';
    protected $description = 'Dispatches the main EOD processing job to the queue.';

    public function handle()
    {
        $this->info('Dispatching EOD job to the queue...');
        ProcessEodJob::dispatch();
        $this->info('Job dispatched successfully.');
        return 0;
    }
}