<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;

class PurgePdfThumbs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-pdf-thumbs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PDF各ページサムネイル(t-*.png)のキャッシュを削除してストレージを解放する（閲覧時にオンデマンド再生成される）';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stats = File::purgePdfThumbCache();
        $mb = number_format($stats['bytes'] / 1024 / 1024, 1);
        $this->info("Deleted {$stats['count']} thumbnail files ({$mb} MB).");
    }
}
