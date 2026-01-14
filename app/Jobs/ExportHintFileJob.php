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

/** 有効な投稿ファイルに、Paper情報に関するヒントファイルを出力 */
class ExportHintFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * PDFのときだけ、ジョブを実行する。see StoreFileRequest
     */
    public function __construct()
    {
        //
        Log::info("ExportHintFileJob dispatched at ".date("Y-m-d H:i:s"));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 最初に、すべてのファイルについて、ヒントファイルを消す。
        $allFiles = File::all();
        foreach($allFiles as $f){
            $f->removeHintFile();
        }

        // 有効な投稿のPDFファイル
        $validPapers = Paper::whereNotNull("pdf_file_id")->get();
        foreach($validPapers as $p){
            $p->writeHintFile();
        }
    }
}
