<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Test9w implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * PDFのときだけ、ジョブを実行する。see StoreFileRequest
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $last9w = Setting::findByIdOrName("LAST_QUEUEWORK_DATE", null); // get object
        $last9w->value = date("Y-m-d H:i:s")." Test9w";
        $last9w->save();
    }
}
