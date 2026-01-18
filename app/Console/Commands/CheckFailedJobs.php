<?php

namespace App\Console\Commands;

use App\Mail\FailedJobsAlert;
use App\Models\FailedJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // $failedJobsCount = DB::table('failed_jobs')->count();
        $failedJobsCount = FailedJob::where('is_read', false)->count();
        if ($failedJobsCount > 0) {
            Mail::to(env('MAIL_BCC_ADDRESS','miuramo@gmail.com'))->send(new FailedJobsAlert($failedJobsCount));
            Log::channel('slack')->error("There are {$failedJobsCount} failed jobs in the system. ".route('admin.failed_jobs', ['all' => 'false']));
            $this->warn("Warning email sent to admin.");
        } else {
            $this->info("No failed jobs found.");
        }
        //
    }
}
