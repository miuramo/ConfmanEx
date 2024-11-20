<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PdfJob implements ShouldQueue
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
        // fnameのフォルダを作成
        // info("PDFジョブ開始");
        $this->file->makeThumbFolder();
        // info("サムネイルフォルダ作成");
        $this->file->makePdfThumbs();
        // info("サムネイル作成");
        $text = $this->file->makePdfText();
        // info("テキスト作成");
        $this->file->makePdfHeadThumb();
        // info("ヘッダーサムネイル作成");
        $this->file->extractTitleAndAuthors($text); //ページ数が2ページ以上のときなど、論文PDFのときに使用
        // info("タイトルと著者抽出、すべて完了。");
    }
}
