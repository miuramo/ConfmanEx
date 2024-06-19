<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Paper;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractTitleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected File $file;
    /**
     * Create a new job instance.
     * PDFのときだけ、ジョブを実行する。see StoreFileRequest
     */
    public function __construct(File $argfile)
    {
        $this->file = $argfile;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // $paper = Paper::find($this->file->paper_id);
        // titletail, authorhead を取得する
        $tail_head = $this->file->getTailHead();

        // $this->file->extractTitleAndAuthors($text); //ページ数が2ページ以上のときなど、論文PDFのときに使用
    }
}
