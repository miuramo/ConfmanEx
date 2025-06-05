<?php

namespace App\Console\Commands;

use App\Mail\GeneralAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckEnqueteDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-enquete-duration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '明日受付開始するアンケートと、今日が最終日のアンケートをチェックする';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $target = \App\Services\EnqueteChecker::tomorrowOpenAndTodayCloseEnqueteConfigs();
        $conftitle = \App\Models\Setting::getval('CONFTITLE');
        if ($target->isEmpty()) {
            $mes = "明日受付開始するアンケートまたは、今日が最終日のアンケートはありません。";
            $this->info($mes);
            // Mail::to(env('MAIL_BCC_ADDRESS', 'miuramo@gmail.com'))->send(new GeneralAlert("[{$conftitle}] {$mes}", $mes));
        } else {
            $this->info("明日受付開始するアンケートまたは、今日が最終日のアンケート:");
            $mes = "";
            foreach ($target as $enqueteConfig) {
                $this->line($enqueteConfig->toString());
                $mes .= $enqueteConfig->toString() . "\n";
            }

            Mail::to(env('MAIL_BCC_ADDRESS', 'miuramo@gmail.com'))->send(new GeneralAlert("[{$conftitle}] 明日受付開始するアンケートまたは、今日が最終日のアンケートがあります。", $mes));
        }
        //
    }
}
