<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Jobs\PdfJob;
use App\Models\File;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        if (Auth::user()->name == User::$initialName) {
            return redirect()->route('user.profile.edit')->with('feedback.success', '氏と名のあいだには半角スペースをいれてください。');
        }

        $all = File::where('user_id', Auth::user()->id)->get()->sortByDesc("id");
        if ($req->ajax()) {
            return view("components.file.elem")->with(compact("all"));
        } else {
            return view("file/index")->with(compact("all"));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("file/create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFileRequest $request)
    {
        return $request->storeFile();
    }

    /**
     * Display the specified resource.
     */
    public function show(int $fileid, string $firsthash)
    {
        if (strlen($firsthash) < 8) abort(403, 'file id and key required');
        try {
            $file = File::where('id', $fileid)->where('key', 'like', $firsthash . "%")->first();
            if ($file == null) {
                abort(403, 'file id and key required');
            }
            // $aT = $file->paper->getAuthorType();
            // if ($aT < 0) abort(403);
            return response()->file(storage_path(File::apf() . '/' . $file->fname));
            // ->header("Content-Disposition", $file->origname);
        } catch (ModelNotFoundException $e) {
            $this->noimage();
            return;
        }
    }
    private function noimage()
    {
        $im = imagecreate(100, 100);
        $bg = imagecolorallocate($im, 200, 200, 100);
        $col = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 5, 15, 40, "No Image", $col);
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }
    public function altimgshow(int $pdffileid, string $firsthash)
    {
        $file = File::where('id', $pdffileid)->where('key', 'like', $firsthash . "%")->first();
        if ($file == null) {
            $this->noimage();
            return;
        }
        // $aT = $file->paper->getAuthorType();
        // if ($aT < 0) abort(403);
        return response()->file(storage_path(File::apf() . '/' . substr($file->fname, 0, -4) . ".png"));
        // ->header("Content-Disposition", $file->origname);
    }
    /**
     * PDFサムネイルを画像で
     */
    public function pdfimages(int $pdffileid, int $pagenum = null, $firsthash = null)
    {
        // 権限の確認
        try {
            $file = File::findOrFail($pdffileid);
            $aT = $file->paper->getAuthorType();
            if ($aT < 0) {
                if (strlen($firsthash) < 12) abort(403, 'file id and key required');
                if (strpos($file->key, $firsthash) !== 0) abort(403, 'file key mismatch');
            }
            if (!is_numeric($pagenum)) {
                return view("file/pdfimages")->with(compact("file"));
            } else {
                return response()->file($file->getPdfThumbPath($pagenum));
            }
            // ->header("Content-Disposition", $file->origname);
        } catch (ModelNotFoundException $e) {
            return "error";
        }
    }
    public function pdftext(int $pdffileid)
    {
        try {
            $file = File::findOrFail($pdffileid);
            $aT = $file->paper->getAuthorType();
            if ($aT < 0) abort(403);
            return response()->file($file->getPdfTextPath(),
                [
                    'Content-Disposition' => 'attachment; filename="'.$file->paper->id_03d()."_".$pdffileid.'.txt"',
                ]
            );
            // ->header("Content-Disposition", $file->origname);
        } catch (ModelNotFoundException $e) {
            return "error";
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileRequest $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $fileid)
    {
        $ones = File::where('user_id', Auth::user()->id)->where('id', $fileid)->get();
        foreach ($ones as $one) {
            $one->remove_the_file();
            $one->delete_me();
            $paper_id = $one->paper_id;
        }
        if (isset($paper_id) && is_numeric($paper_id) && $paper_id > 0) {
            return redirect()->route('paper.edit', ['paper' => $paper_id])->with('feedback.success', 'ファイルを削除しました');
        }
        return redirect()->route('file.index')->with('feedback.success', 'ファイルを削除しました');
    }

    /**
     * DB.files のデータを消す。ついでに、ファイルも消す。
     */
    public function delall()
    {
        // まずはお行儀よく、DB.files のデータに紐づいているファイルを消す。
        $all = File::where('user_id', Auth::user()->id)->get()->sortByDesc("id");
        foreach ($all as $f) {
            $f->remove_the_file();
            $f->delete_me();
        }
        return redirect()->route('file.index')->with('feedback.success', 'ファイルを削除しました');
    }

    /**
     * 投稿ファイルのうち、Paperに紐づいている有効なものを「ロック状態」にする。ただし、Pendingはロックしない。
     */
    public function adminlock(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        if ($req->method() === 'POST') {

            if ($req->has('action')) { // action is lock or unlock
                // targetmime の value をあつめる
                $targetmimes = [];
                foreach ($req->all() as $k => $v) {
                    if (strpos($k, "targetmime") === 0) { // == 0 だと、false がふくまれてしまう。
                        $targetmimes[$v] = 1;
                    }
                }
                // targetmainpdf 
                $targetmainpdf = $req->has("targetmainpdf");
                foreach ($req->all() as $k => $v) {
                    if (strpos($k, "targetcat") === 0) { // == 0 だと、false がふくまれてしまう。

                        // 採択状態を調査
                        $acc = MailTemplate::mt_accept(intval($v));
                        $rej = MailTemplate::mt_reject(intval($v));
                        $papers = Paper::with("pdf_file")->where("category_id", $v)->get();
                        $ta = $req->input("targetaccept");
                        if ($ta == "accepted") {
                            $papers = $acc;
                        } else if ($ta == "rejected") {
                            $papers = $rej;
                        }
                        foreach ($papers as $paper) {
                            if (is_numeric($paper)) {
                                $paper = Paper::with("pdf_file")->find($paper);
                            }
                            if ($paper->pdf_file_id && $targetmainpdf) {
                                $paper->pdf_file->locked = ($req->input('action') === 'lock');
                                $paper->pdf_file->save();
                            }
                            // サプリメントファイルを操作する。ただし、PaperPDFは除外する。
                            $files = File::where("paper_id", $paper->id)->whereNot("id", $paper->pdf_file_id)->whereIn("mime", array_keys($targetmimes))->get();
                            foreach ($files as $file) {
                                if ($file->id == $paper->pdf_file_id) continue; // PaperPDFは除外
                                $file->locked = ($req->input('action') === 'lock');
                                $file->save();
                            }
                        }
                    }
                }
            }
            return redirect()->route('file.adminlock')->with('feedback.success', "選択カテゴリの投稿ファイルを{$req->input('action')}にしました。（ただし、deleted=0, pending=0が対象）");
        }

        // 集約でカウント→cnt 
        $fs = ["files.valid", "files.deleted", "files.pending", "files.locked"];
        $sql1 = "select count(files.id) as cnt, " . implode(",", $fs);
        $sql1 .= " ,category_id from files left join papers on files.paper_id = papers.id group by " . implode(",", $fs);
        $sql1 .= " ,category_id order by deleted, category_id, " . implode(",", $fs);
        $cols = DB::select($sql1);

        // 個別項目
        $sql2 = "select paper_id, files.id, mime, pagenum, files.key, " . implode(",", $fs);
        $sql2 .= " ,category_id from files left join papers on files.paper_id = papers.id order by category_id, paper_id, " . implode(",", $fs);
        $res2 = DB::select($sql2);
        $pids = [];
        $fileids = [];
        $filekeys = [];
        foreach ($res2 as $res) {
            $shortmime = explode("/", $res->mime)[1];
            if (!is_array(@$pids[$res->category_id][$res->valid][$res->deleted][$res->pending][$res->locked])) {
                $pids[$res->category_id][$res->valid][$res->deleted][$res->pending][$res->locked] = [];
            }
            $label = sprintf("%03d", $res->paper_id) . " (f{$res->id} {$shortmime}";
            if ($res->mime === 'application/pdf'){
                $label .= $res->pagenum."p";
            }
            $label .= ")";
            $pids[$res->category_id][$res->valid][$res->deleted][$res->pending][$res->locked][] = $label;

            $fileids [$label] = $res->id;
            $filekeys [$label] = $res->key;
        }
        // dd($pids);


        return view('admin.filelock')->with(compact("cols", "pids","fileids","filekeys"));
    }

    public function favicon()
    {
        $im = imagecreatefrompng(public_path("favicon.png"));

        $year = Setting::findByIdOrName("CONFTITLE_YEAR", "value");
        if ($year) {
            $year_02d = $year % 100;
            $fabcolors = Setting::firstOrCreate([
                'name' => "FAVICON_COLORS",
            ], [
                'value' => "[207,48,48,  252,204,204]",
                'isnumber' => false,
                'isbool' => false,
            ]);
            // a:6:{i:0;i:207;i:1;i:48;i:2;i:48;i:3;i:252;i:4;i:204;i:5;i:204;} red
            // a:6:{i:0;i:48;i:1;i:207;i:2;i:48;i:3;i:204;i:4;i:252;i:5;i:252;} green
            // a:6:{i:0;i:48;i:1;i:48;i:2;i:255;i:3;i:204;i:4;i:252;i:5;i:252;} blue
            $fca = (strpos($fabcolors->value, "i:")) ? unserialize($fabcolors->value) : json_decode($fabcolors->value);
            $bgc = imagecolorallocate($im, $fca[0], $fca[1], $fca[2]);
            $fgc = imagecolorallocate($im, $fca[3], $fca[4], $fca[5]);
            $dejavu = public_path('font/DejaVuSans.ttf');
            // Yearの下2桁を書き込む
            for ($i = -2; $i < 3; $i++) {
                for ($j = -2; $j < 3; $j++) {
                    ImageTTFText($im, 16, 0, 6 + $i, 28 + $j, $bgc, $dejavu, $year_02d);
                }
            }
            ImageTTFText($im, 16, 0, 6, 28, $fgc, $dejavu, $year_02d);
        }

        header("Content-Type: image/png");
        imageAlphaBlending($im, false);
        imageSaveAlpha($im, true);
        imagepng($im);
        imagedestroy($im);

        // return response()->file(storage_path(File::apf() . '/' . substr($file->fname, 0, -4) . ".png"));
    }
}
