<?php

namespace App\Models;

use App\Jobs\PdfJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Normalizer;

class File extends Model
{
    use HasFactory;

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    // public static $app_public_files = 'app/public/';
    // public static $public_files = 'public';
    public static $filedir = 'zzz2024';
    public static function apf(){
        File::$filedir = Setting::where('name', 'FILEPUT_DIR')->first()['value'];// Config::get('fileput.dir');
        if (strlen(File::$filedir)==0) File::$filedir = env('FILEPUT_DIR', 'plz_set_Setting_FILEPUT_DIR');
        return 'app/public/' . File::$filedir;
    }
    public static function pf(){
        File::$filedir = Setting::where('name', 'FILEPUT_DIR')->first()['value'];
        if (strlen(File::$filedir)==0) File::$filedir = env('FILEPUT_DIR', 'plz_set_Setting_FILEPUT_DIR');
        return 'public/'. File::$filedir;
    }

    /**
     * モデルの属性のデフォルト値
     *
     * @var array
     */
    protected $attributes = [
        'mime' => 'image/png',
        'key' => 'xxx',
        'user_id' => 1,
        'origname' => 'img.png',
        'pagenum' => 0,
    ];

    public function remove_the_file()
    {
        $fullpath = $this->fullpath();
        $ret = @unlink($fullpath); // Storage::delete( $fullpath );
        // if ($ret) {
        //     // Log::info("remove_the_file : " . $fullpath);
        // } else {
        //     // Log::info("failed remove : " . $fullpath);
        // }
        if ($this->mime == "application/pdf") {
            @unlink(substr($fullpath, 0, -4) . ".png");
            $this->removeDirectory(substr($fullpath, 0, -4));
        }
    }
    public function fullpath()
    {
        return storage_path(File::apf() .'/'. $this->fname);
    }
    public function extension()
    {
        return pathinfo($this->fname, PATHINFO_EXTENSION);
    }

    public function delete_me()
    {
        File::destroy($this->id);
    }

    public function makeThumbFolder()
    {
        $dir = substr($this->fname, 0, -4);
        return File::mkdir_ifnot(storage_path(File::apf() .'/'. $dir));
    }

    public static function mkdir_ifnot($dirpath){
        if (!file_exists($dirpath)) {
            return mkdir($dirpath,0777,true);
        }
        return true;
    }

    public function makePdfHeadThumb()
    {
        $fullpath_pdf = storage_path(File::apf() .'/'. $this->fname); // 元のPDFファイル名
        $dir = substr($this->fname, 0, -4);
        $fullpath_png = storage_path(File::apf() .'/'. $dir . ".png"); // PNGファイル名
        $dirpath = storage_path(File::apf() .'/'. $dir); // /でおわらないので注意
        // 1ページ目の上だけのサムネ
        // $file->pagenum = preg_match_all("/\/Page\W/", $data, $matches);
        // ここで、convertをつかって、t-00001.png の上部だけを取り出した画像ファイル h-00001.png を作成する
        // 画像のオリジナルサイズは1241x1754
        $orig_w = 1241;
        $orig_h = 1754;
        $crop_w = 900;
        $crop_x = intval(($orig_w - $crop_w) / 2);
        $crop_h = 400;
        $crop_y = 180;

        File::mkdir_ifnot($dirpath);
        chdir($dirpath);
        while (!file_exists($fullpath_png)) {
            sleep(1);
        }
        // Log::info("convert {$fullpath_png} -crop {$crop_w}x{$crop_h}+{$crop_x}+{$crop_y} {$dirpath}/h-00001.png");
        $out = shell_exec("convert {$fullpath_png} -crop {$crop_w}x{$crop_h}+{$crop_x}+{$crop_y} {$dirpath}/h-00001.png 2>&1");

    }


    /**
     * PDFのサムネイルをつくる
     */
    public function makePdfThumbs()
    {
        $fullpath = storage_path(File::apf() .'/'. $this->fname); // 元のファイル名
        $dir = substr($this->fname, 0, -4);
        $dirpath = storage_path(File::apf() .'/'. $dir); // /でおわらないので注意
        $newpath = storage_path(File::apf() .'/'. $dir . '/_.pdf');
        link($fullpath, $newpath);

        shell_exec("pdftoppm -png {$newpath} {$dirpath}/t");

        unlink($newpath);

        // ファイル名を調整する。ゼロを埋める
        chdir($dirpath);
        foreach (glob($dirpath . "/*.png") as $fullpathfn) {
            // 数字部分をとりだす
            $fn = basename($fullpathfn);
            $num = substr($fn, 2, -4);
            rename($fullpathfn, sprintf("t-%05d.png", $num));
        }
    }
    // cropした画像
    public function getPdfHeadPath()
    {
        $h_00001 = sprintf("h-%05d.png", 1);
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() .'/'. $dir . "/" . $h_00001);
    }
    public function getPdfThumbPath(int $pagenum)
    {
        $page05f = sprintf("t-%05d.png", $pagenum);
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() .'/'. $dir . "/" . $page05f);
    }
    public function getPdfTextPath()
    {
        $page05f = "p-00001.txt";
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() .'/'. $dir . "/" . $page05f);
    }
    public function getPdfText(){
        $fn = $this->getPdfTextPath();
        $txtf = fopen($fn,"r");
        if ($txtf){
            return fread($txtf, filesize($fn));
        }
        return null;
    }
    public function makePdfText(): string
    {
        $dirpath = storage_path(File::apf());
        $text = $this->getstdout($dirpath, "pdftotext -f 1 -l 1 " . $this->fname . " -");
        $text = $this->remove_mb4($text);
        $text = Normalizer::normalize($text, Normalizer::FORM_C);

        $dir = substr($this->fname, 0, -4);
        $dirpath = storage_path(File::apf() .'/'. $dir);
        $txtpath = $dirpath . "/p-00001.txt";
        $txtf = fopen($txtpath, "w");
        if ($txtf) {
            fwrite($txtf, $text);
            fclose($txtf);
        }
        return $text;
    }
    //ページ数が2ページ以上のときなど、論文PDFのときに使用 PDFJob
    public function extractTitleAndAuthors(string $text)
    {
        if ($this->pagenum < 2) return;
        // Log::info("[File@extractTitle] File id: {$this->id} pagenum {$this->pagenum} start title extracts");
        Paper::find($this->paper_id)->extractTitleAndAuthors($text);
    }
    /**
     * 標準出力を取り出して、返却値とする
     */
    public function getstdout($dir, $cmd)
    {
        chdir($dir);
        $cmdh = popen($cmd, "r");
        $cmdret = '';
        while (!feof($cmdh)) {
            $cmdret .= fread($cmdh, 8192);
        }
        return $cmdret;
    }

    public function remove_mb4($str)
    {
        $str = preg_replace('/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', '', $str);
        return $str;
    }


    public function removeDirectory($path)
    {
        if (!is_dir($path)) {
            return;
        }
        // Log::info("[File@removeDir] remove_the_dir_ : " . $path);
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir("$path/$file")) {
                    $this->removeDirectory("$path/$file");
                } else {
                    @unlink("$path/$file");
                }
            }
        }
        @rmdir($path);
    }

    public static function kanjifn($str, $substr = 0, $type = "guess")
    {
        $fn = null;
        $ostype = $type;
        if ($type == "guess") {
            $ua = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/Macintosh/', $ua)) {
                $ostype = "Mac";
            }
            if (preg_match('/Windows/', $ua)) {
                $ostype = "Win";
            }
            if (preg_match('/Linux/', $ua)) {
                $ostype = "Linux";
            }
        }
        $encode = "sjis-win";
        if ($ostype == "Win") {
            $encode = "sjis-win";
        }
        if ($ostype == "Mac") {
            $encode = "utf-8";
        }
        if ($ostype == "Linux") {
            $encode = "utf-8";
        }
        if ($type == "utf-8" || $type == "utf8") {
            $encode = "utf-8";
        }
        $fn1 = str_replace([' ', '　'], '', trim($str));
        $fn1 = Normalizer::normalize($fn1, Normalizer::FORM_C); //winだがmacもOK?
        if ($substr > 0) {
            $fn = mb_substr($fn, $substr);
        }
        $fn1 = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|', '﻿—'], '_', $fn1);
        $fn = mb_convert_encoding($fn1, $encode, "utf-8");

        return $fn;
    }

    /**
     * サムネイルとタイトルの再構成
     */
    public static function rebuildPDFThumb()
    {
        $pdfs = File::where("mime","application/pdf")->get();
        foreach($pdfs as $pdf){
            if ($pdf->someLostFiles()){
                echo "rebuild file id:{$pdf->id}\n";
                PdfJob::dispatch($pdf);
            }
        }
    }
    /**
     * サーバ側でPDFから生成するファイルについて、不足があればtrueをかえす。
     * 注：PDFのみ意味がある。ビデオや画像には適用できない。
     */
    public function someLostFiles()
    {
        $dir = substr($this->fname, 0, -4);
        if (!file_exists(storage_path(File::apf() .'/'. $dir))) return true;

        if (!file_exists($this->getPdfHeadPath())) return true;
        $pages = $this->pagenum;
        for($i=1;$i<=$pages;$i++){
            if (!file_exists($this->getPdfThumbPath($i))) return true;
        }
        if (!file_exists($this->getPdfTextPath())) return true;
        return false;
    }
}
