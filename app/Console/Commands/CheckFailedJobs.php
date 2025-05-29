<?php

namespace App\Console\Commands;

use App\Mail\FailedJobsAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-failed-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for failed jobs and alert admin if any exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $failedJobsCount = DB::table('failed_jobs')->count();
        if ($failedJobsCount > 0) {
            Mail::to(env('MAIL_BCC_ADDRESS','miuramo@gmail.com'))->send(new FailedJobsAlert($failedJobsCount));
            $this->warn("Warning email sent to admin.");
        } else {
            $this->info("No failed jobs found.");
        }
        //
    }
}
