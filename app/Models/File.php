<?php

namespace App\Models;

use App\Jobs\OcrJob;
use App\Jobs\PdfJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Normalizer;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'paper_id',
        'fname',
        'mime',
        'key',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    /**
     * 
     * BBMesControllerから呼ばれる
     */
    public static function createnew($tmp, $pid = 0)
    {
        // フォルダがなければ作る
        File::mkdir_ifnot(storage_path(File::apf()));

        $file = new File();
        $file->user_id = Auth::user()->id;
        $pid = $file->paper_id = $pid;
        // fnameは暫定として、一回保存して、fileid を確定する
        $file->fname = "zantei.pdf";
        $file->save();
        $hashname = sprintf("%03d", $pid) . "_" . $file->id . "_" . $tmp->hashName();
        $tmp->storeAs(File::pf(), $hashname);

        $file->fname = $hashname;
        $fullpath = storage_path(File::apf() . '/' . $hashname);
        $file->key = shell_exec("md5sum {$fullpath}");
        $file->key = substr($file->key, 0, 32);
        $file->mime = trim(shell_exec("file --mime-type -b {$fullpath}")); // $tmp->getClientMimeType();
        $file->origname = $tmp->getClientOriginalName();

        // ページ番号を取得
        $pdfinfo = trim(shell_exec("pdfinfo {$fullpath}"));
        $ary = explode("\n", $pdfinfo);
        $pnum = -1;
        foreach ($ary as $n => $p) {
            if (preg_match('/^Pages:[ ]+(\d+)/', $p, $match)) {
                $pnum = $match[1];
            }
        }
        $file->pagenum = $pnum;
        $file->save();
        // 1ページ目のサムネをつくる
        shell_exec("pdftoppm -png -singlefile {$fullpath} " . storage_path(File::apf() . '/' . substr($hashname, 0, -4)));

        if ($file->mime == "application/pdf") {
            PdfJob::dispatch($file);
        }
        return $file;
    }

    // public static $app_public_files = 'app/public/';
    // public static $public_files = 'public';
    public static $filedir = 'zzz2024';
    public static function apf()
    {
        File::$filedir = Setting::where('name', 'FILEPUT_DIR')->first()['value']; // Config::get('fileput.dir');
        if (strlen(File::$filedir) == 0) File::$filedir = env('FILEPUT_DIR', 'plz_set_Setting_FILEPUT_DIR');
        return 'app/public/' . File::$filedir;
    }
    public static function pf()
    {
        File::$filedir = Setting::where('name', 'FILEPUT_DIR')->first()['value'];
        if (strlen(File::$filedir) == 0) File::$filedir = env('FILEPUT_DIR', 'plz_set_Setting_FILEPUT_DIR');
        return 'public/' . File::$filedir;
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
        if (strpos($this->mime, "video") === 0) {
            @unlink(substr($fullpath, 0, -4) . ".png");
        }
    }
    public function fullpath()
    {
        if (file_exists(storage_path(File::apf() . '/' . $this->fname))) {
            return storage_path(File::apf() . '/' . $this->fname);
        } else {
            if (endsWith($this->fname, ".png")) { // fallback if thumbnail png is missing
                return storage_path(File::apf() . '/' . substr($this->fname, 0, -4) . '/t-00001.png');
            }
        }
        return storage_path(File::apf() . '/nofile.png'); // NoFile
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
        return File::mkdir_ifnot(storage_path(File::apf() . '/' . $dir));
    }

    public static function mkdir_ifnot($dirpath)
    {
        if (!file_exists($dirpath)) {
            return mkdir($dirpath, 0777, true);
        }
        return true;
    }

    public function makePdfHeadThumb()
    {
        $fullpath_pdf = storage_path(File::apf() . '/' . $this->fname); // 元のPDFファイル名
        $dir = substr($this->fname, 0, -4);
        $fullpath_png = storage_path(File::apf() . '/' . $dir . ".png"); // PNGファイル名
        $dirpath = storage_path(File::apf() . '/' . $dir); // /でおわらないので注意
        // 1ページ目の上だけのサムネ
        // $file->pagenum = preg_match_all("/\/Page\W/", $data, $matches);
        // ここで、convertをつかって、t-00001.png の上部だけを取り出した画像ファイル h-00001.png を作成する
        // 画像のオリジナルサイズは1241x1754
        $orig_w = 1241;
        $orig_h = 1754;
        $crop_yhwx_setting = Setting::getval("CROP_YHWX");
        $crop_yhwx = json_decode($crop_yhwx_setting);
        $crop_y = $crop_yhwx[0];
        $crop_h = $crop_yhwx[1];
        $crop_w = $crop_yhwx[2];
        $crop_x = $crop_yhwx[3];
        if ($crop_x < 0) {
            $crop_x = intval(($orig_w - $crop_w) / 2);
        }
        File::mkdir_ifnot($dirpath);
        chdir($dirpath);
        $retry = 0;
        while (!file_exists($fullpath_png)) {
            sleep(1);
            $retry++;
            if ($retry > 10) {
                Log::error("File@makePdfHeadThumb before crop : no file {$fullpath_png} (pdftoppm failed?)");
                break;
            }
        }
        // Log::info("convert {$fullpath_png} -crop {$crop_w}x{$crop_h}+{$crop_x}+{$crop_y} {$dirpath}/h-00001.png");
        $out = shell_exec("convert {$fullpath_png} -crop {$crop_w}x{$crop_h}+{$crop_x}+{$crop_y} {$dirpath}/h-00001.png 2>&1");
        if (strlen($out) > 1) Log::info($out);
    }


    /**
     * PDFのサムネイルをつくる
     */
    public function makePdfThumbs()
    {
        $fullpath = storage_path(File::apf() . '/' . $this->fname); // 元のファイル名
        $dir = substr($this->fname, 0, -4);
        $dirpath = storage_path(File::apf() . '/' . $dir); // /でおわらないので注意
        $newpath = storage_path(File::apf() . '/' . $dir . '/_.pdf');
        link($fullpath, $newpath);

        shell_exec("pdftoppm -png {$newpath} {$dirpath}/t");

        // if file exists, unlink
        @unlink($newpath);

        // ファイル名を調整する。ゼロを埋める
        if (!file_exists($dirpath)) {
            // pdftoppmが失敗している
            info("File.php:205 makePdfThumbs: no dirpath {$dirpath} (pdfoppm failed?)");
            return;
        }
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
        return storage_path(File::apf() . '/' . $dir . "/" . $h_00001);
    }
    public function getPdfThumbPath(int $pagenum)
    {
        $page05f = sprintf("t-%05d.png", $pagenum);
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() . '/' . $dir . "/" . $page05f);
    }
    public function getPdfTextPath()
    {
        $page05f = "p-00001.txt";
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() . '/' . $dir . "/" . $page05f);
    }
    public function getPdfText()
    {
        $fn = $this->getPdfTextPath();
        return $this->read_textfile($fn);
    }
    public function read_textfile($fn)
    {
        $txtf = fopen($fn, "r");
        if ($txtf) {
            return fread($txtf, filesize($fn));
        }
        return null;
    }
    public function write_textfile($fn, $txt)
    {
        $txtf = fopen($fn, "w");
        if ($txtf) {
            return fwrite($txtf, $txt);
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
        $dirpath = storage_path(File::apf() . '/' . $dir);
        $txtpath = $dirpath . "/p-00001.txt";

        // ディレクトリができるのを待つ
        $retry = 0;
        while (!file_exists($dirpath)) {
            sleep(1);
            $retry++;
            if ($retry > 10) {
                Log::error("File@makePdfText: no dirpath {$dirpath} (pdftoppm failed?)");
                break;
            }
        }
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
        if ($this->paper_id > 0) Paper::find($this->paper_id)->extractTitleAndAuthors($text);
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


    public static function removeDirectory($path)
    {
        if (!is_dir($path)) {
            return;
        }
        // Log::info("[File@removeDir] remove_the_dir_ : " . $path);
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir("$path/$file")) {
                    self::removeDirectory("$path/$file");
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
        $pdfs = File::where("mime", "application/pdf")->get();
        foreach ($pdfs as $pdf) {
            if ($pdf->someLostFiles()) {
                echo "rebuild file id:{$pdf->id}\n";
                PdfJob::dispatch($pdf);
            }
        }
    }
    /**
     * タイトル部分画像のみを再構成（注: 現状は画像も再生成してしまう）
     */
    public function altimg_recrop()
    {
        @unlink($this->getPdfHeadPath());
        PdfJob::dispatch($this);
    }

    /**
     * サーバ側でPDFから生成するファイルについて、不足があればtrueをかえす。
     * 注：PDFのみ意味がある。ビデオや画像には適用できない。
     */
    public function someLostFiles()
    {
        $dir = substr($this->fname, 0, -4);
        if (!file_exists(storage_path(File::apf() . '/' . $dir))) return true;

        if (!file_exists($this->getPdfHeadPath())) return true;
        $pages = $this->pagenum;
        for ($i = 1; $i <= $pages; $i++) {
            if (!file_exists($this->getPdfThumbPath($i))) return true;
        }
        if (!file_exists($this->getPdfTextPath())) return true;
        return false;
    }
    public static function rebuildOcrTsv()
    {
        $pdfs = File::where("mime", "application/pdf")->get();
        foreach ($pdfs as $pdf) {
            if (!file_exists($pdf->getPdfOcrTsvPath())) {
                echo "rebuild ocr: file id {$pdf->id}\n";
                OcrJob::dispatch($pdf);
            }
        }
    }
    public function getPdfOcrTsvPath()
    {
        $page05f = "h-00001.tsv";
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() . '/' . $dir . "/" . $page05f);
    }
    public function makeOcrTsv()
    {
        $dir = substr($this->fname, 0, -4);
        $dirpath = storage_path(File::apf() . '/' . $dir); // /でおわらないので注意
        chdir($dirpath);
        shell_exec("tesseract h-00001.png h-00001 -l jpn tsv ");
        // shell_exec("tesseract h-00001.png h-00001 --psm 6 -l jpn+eng tsv ");
        shell_exec("tesseract t-00001.png t-00001 -l jpn tsv ");
    }

    public function getTailHead()
    {
        // $paper = Paper::find($this->file->paper_id);
        // if ($paper->pdf_file_id != $this->id) return "unlink";
        // return $this->getPdfOcrTsvPath();
        //   0 => "level"
        //   1 => "page_num"
        //   2 => "block_num"
        //   3 => "par_num"
        //   4 => "line_num"
        //   5 => "word_num"
        //   6 => "left"
        //   7 => "top"
        //   8 => "width"
        //   9 => "height"
        //   10 => "conf"
        //   11 => "text"
        $ocrtxt = $this->read_textfile($this->getPdfOcrTsvPath());
        $lines = explode("\n", $ocrtxt);
        $lines = array_map("trim", $lines);
        $l2i = []; // すぐに置き換えられる
        $i2l = [];
        $words = [];
        foreach ($lines as $n => $line) {
            // tab
            $ary = explode("\t", $line);
            $ary = array_map("trim", $ary);
            $tmp = [];
            if ($n == 0) {
                $i2l = $ary;
                $l2i = array_flip($ary);
            } else {
                foreach ($i2l as $i => $l) {
                    $tmp[$l] = $ary[$i];
                }
                // 下準備完了
                if ($tmp['level'] == 5 && $tmp['text'] != null) {
                    $words[] = $tmp;
                }
            }
        }
        // return $i2l;
        return $words;
    }

    public function getHintFilePath()
    {
        $fn = "hint.txt";
        $dir = substr($this->fname, 0, -4);
        return storage_path(File::apf() . '/' . $dir . "/" . $fn);
    }

    public function removeHintFile()
    {
        @unlink($this->getHintFilePath());
        // $this->writeHintFile("xx");
    }
    public function writeHintFile($txt)
    {
        $this->write_textfile($this->getHintFilePath(), $txt);
    }

    public function getFileSize()
    {
        $fullpath = $this->fullpath();
        if (file_exists($fullpath)) {
            return filesize($fullpath);
        }
        return 0;
    }

    public static function getRealFileNames()
    {
        $parentdir = storage_path(File::apf());
        // ファイル一覧
        $files = scandir($parentdir);
        $files = array_diff($files, ['.', '..', '.DS_Store', 'dump.sql', 'nofile.png', 'passdumpsql.zip']);
        return $files;
    }

    public static function getRealFolderNames()
    {
        $parentdir = storage_path(File::apf());
        $list = self::getRealFileNames();
        $folders = [];
        foreach ($list as $file) {
            if ($file == "." || $file == "..") continue;
            if (is_dir($parentdir . "/" . $file)) {
                $folders[] = $file;
            }
        }
        return $folders;
    }
    public static function getFileNamesNotInDB()
    {
        $fnames = self::getRealFileNames();
        $notindb = [];
        $indb = [];
        foreach ($fnames as $fname) {
            $base = basename($fname);
            $f = File::where("fname", "like", $base . "%")->first(); //softdeleteはつかっていない。
            if ($f) {
                $indb[$f->id] = $fname;
            } else {
                $notindb[] = $fname;
            }
        }
        return ['notindb' => $notindb, 'indb' => $indb];
    }
    public static function delete_notindb()
    {
        $folders = self::getFileNamesNotInDB();
        $notindb = $folders['notindb'];
        foreach ($notindb as $filename) {
            $fullpath = storage_path(File::apf() . '/' . $filename);
            if (is_dir($fullpath)) {
                self::removeDirectory($fullpath);
            } else {
                @unlink($fullpath);
            }
            // // getRealFileNames();から検索する
            // foreach($realfiles as $file) {
            //     if (preg_match("/^{$filename}/", $file)) {
            //         $fullpath = storage_path(File::apf() . '/' . $file);
            //         if (is_file($fullpath)) {
            //             @unlink($fullpath);
            //         }
            //     }
            // }
            // info($realfiles);
        }
    }
    /**
     * PDFにフォントが埋め込まれているかどうかをチェックする
     */
    public function font_not_embedded()
    {
        if ($this->mime != "application/pdf") {
            return null; // PDF以外は、フォント埋め込みの概念がないので、nullを返す
        }
        $fullpath = $this->fullpath();
        $out = shell_exec("pdffonts {$fullpath}");
        $lines = preg_split("/\r\n|\n|\r/", $out);
        // parse: find header line containing 'name' and 'emb'
        $headerIndex = -1;
        $headerTokens = [];
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            // detect header line: must contain 'name' and 'emb' tokens (case-insensitive)
            if (preg_match('/\bname\b/i', $line) && preg_match('/\bemb\b/i', $line)) {
                $headerIndex = $i;
                // split header by whitespace
                $headerTokens = preg_split('/\s+/', $line);
                break;
            }
        }

        if ($headerIndex === -1) {
            Log::channel("single")->error("Error: pdffonts のヘッダ行を検出できませんでした。\n期待されるヘッダ（例: name type encoding emb sub uni object ID）を含む行が必要です。\n");
            return false;
        }

        // determine index of important columns
        $lowerHeader = array_map('strtolower', $headerTokens);
        $embCol = array_search('emb', $lowerHeader);
        $nameCol = array_search('name', $lowerHeader);
        $typeCol = array_search('type', $lowerHeader); // optional

        if ($embCol === false || $nameCol === false) {
            Log::channel("single")->error("Error: ヘッダに 'emb' または 'name' カラムが見つかりません。\n");
            return false;
        }

        // collect non-embedded fonts
        $nonEmbedded = [];
        for ($i = $headerIndex + 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (trim($line) === '') continue;
            // skip separator lines like "---- ----"
            if (preg_match('/^[-\s]+$/', trim($line))) continue;

            // split by whitespace
            $tokens = preg_split('/\s+/', trim($line));

            // sometimes object ID column may contain two numbers, causing token count to vary.
            // we assume name is first token and emb at embCol (by header mapping) when possible.
            if (count($tokens) < max($embCol, $nameCol) + 1) {
                // fallback: try to pad tokens by merging trailing ones (best-effort)
                // but if we can't parse, skip line with warning
                Log::channel("single")->error("Warning: 行を解析できません (トークン不足): '{$line}'\n");
                continue;
            }

            $embVal = strtolower($tokens[$embCol]);
            $fontName = $tokens[$nameCol];

            // If font name may include spaces (rare), try to reconstruct using column widths:
            // (Advanced parsing omitted here; pdffonts usually uses single-token font names.)
            if ($embVal !== 'yes' && $embVal !== 'no') {
                // sometimes header positions differ; attempt to find first 'yes'/'no' token in line
                $found = false;
                foreach ($tokens as $ti => $tk) {
                    $lk = strtolower($tk);
                    if ($lk === 'yes' || $lk === 'no') {
                        $embVal = $lk;
                        // assume font name is token 0
                        $fontName = $tokens[0];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    Log::channel("single")->error("Warning: emb 値 (yes/no) を行から見つけられませんでした: '{$line}'\n");
                    continue;
                }
            }

            if ($embVal !== 'yes') {
                // collect extra info (type if available)
                $typeVal = ($typeCol !== false && isset($tokens[$typeCol])) ? $tokens[$typeCol] : '';
                $nonEmbedded[] = ['name' => $fontName, 'emb' => $embVal, 'type' => $typeVal, 'raw' => $line];
            }
        }

        if (count($nonEmbedded) > 0) {
            Log::channel("single")->info("非埋め込みフォントが見つかりました:\n");
            foreach ($nonEmbedded as $font) {
                Log::channel("single")->info("- フォント名: {$font['name']}, 埋め込み: {$font['emb']}, タイプ: {$font['type']}\n  行内容: {$font['raw']}\n");
            }
            return $nonEmbedded;
        }
        return $nonEmbedded; // empty array means all fonts are embedded
    }
}
