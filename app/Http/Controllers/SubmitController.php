<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoresubmitRequest;
use App\Http\Requests\UpdatesubmitRequest;
use App\Models\Accept;
use App\Models\Category;
use App\Models\Paper;
use App\Models\Setting;
use App\Models\Submit;
use Illuminate\Http\Request;
use ZipArchive;

class SubmitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoresubmitRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(submit $submit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(submit $submit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatesubmitRequest $request, submit $submit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(submit $submit)
    {
        //
    }



    /**
     * 出版担当またはプログラムチェアによる、プログラム編成とブース設定
     */
    public function booth(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub')) abort(403);

        if ($req->method() === 'POST') {
            if (!preg_match("/%[0-9]*d/", $req->input("print_format"))) return "ERROR: sprintfフォーマットを見直してください。" . $req->input("print_format");
            $ary = json_decode($req->input("json"), true);
            $num = 1;
            foreach ($ary as $sessionid => $presens) { // [0=>pid1, 1=>pid2, ...]
                foreach ($presens as $pid) {
                    $sub = Submit::where("category_id", $catid)->where("paper_id", $pid)->first();
                    $sub->psession_id = $sessionid;
                    $sub->orderint = $num;
                    if ($req->has("copy_orderint_to_booth")) $sub->booth = sprintf($req->input("print_format"), $num);
                    $sub->save();
                    $num++;
                }
            }
            return "OK"; 
        }

        $subs = Submit::with('paper')->where("category_id", $catid)->where("accept_id", "<", 20)->orderBy("orderint")->get();
        return view('pub.booth', ["cat" => $catid])->with(compact("subs"));
    }

    public function boothtxt(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub')) abort(403);

        $sbmap = "";
        if ($req->method() === 'POST') {
            $sbmap = $req->input("sbmap");
            $lines = explode("\n", trim($sbmap));
            $lines = array_map("trim", $lines);
            $lines = array_filter($lines, function ($v) {
                return $v !== "";
            });

            $paper_session_map = [];
            $paper_booth_map = [];
            $booth_paper_map = [];
            foreach ($lines as $n => $line) {
                if (strpos($line, "#") === 0) continue; // skip comment
                $line = str_replace("|", "\t", $line);
                $line = preg_replace("/[\t]+/", "\t", $line); //複数まとめる
                $ary = explode("\t", $line);
                $ary = array_map("trim", $ary);
                $ary = array_filter($ary, function ($v) { // 空白をとりのぞく
                    return $v !== "";
                });
                if (count($ary) !== 3) {
                    return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.error', ($n + 1) . '行目付近にエラーがあります。要素は3つである必要があります。');
                }
                //
                if (!is_numeric($ary[0]) || !is_numeric($ary[1])){
                    return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.error', ($n + 1) . '行目付近にエラーがあります。sessionidとpaperidは整数である必要があります。');
                }
                if (isset($paper_session_map[$ary[1]])) {
                    return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.error', ($n + 1) . '行目付近にエラーがあります。PaperIDに重複があります。(' . $ary[1] . ')');
                } else {
                    $paper_session_map[$ary[1]] = $ary[0];
                }
                if (isset($booth_paper_map[$ary[2]])) {
                    return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.error', ($n + 1) . '行目付近にエラーがあります。ブースに重複があります。(' . $ary[2] . ')');
                } else {
                    $booth_paper_map[$ary[2]] = $ary[1];
                }
                $paper_booth_map[$ary[1]] = $ary[2];
            }
            //割り当て実行
            foreach($paper_session_map as $paperid=>$sessionid){
                $sub = Submit::where("category_id",$catid)->where("paper_id", $paperid)->first();
                $sub->booth = $paper_booth_map[$paperid];
                $sub->psession_id = $sessionid;
                $sub->save();
            }
            // orderint を自動で更新
            $subs = Submit::with('paper')->where("category_id", $catid)->where("accept_id", "<", 20)->orderBy("booth")->get();
            $num = 1;
            foreach($subs as $sub){
                $sub->orderint = $num;
                $sub->save();
                $num++;
            }
            return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.success', '割り当て実行しました。');
        }
        $subs = Submit::with('paper')->where("category_id", $catid)->where("accept_id", "<", 20)->orderBy("orderint")->get();
        return view('pub.boothtxt', ["cat" => $catid])->with(compact("subs", "sbmap"));
    }


    /**
     * ZIP file download for publication
     */
    public function zipdownload(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub')) abort(403);
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        $filetypes = []; // pdf, video, img, altpdf
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
            if (strpos($k, "filetype") === 0) $filetypes[] = $v;
        }
        // 採択submits→paper_id list
        $accept_ids = Accept::where('judge', '>', 0)->pluck("id")->toArray();
        $accept_papers = Submit::whereIn('accept_id', $accept_ids)->pluck("paper_id")->toArray();

        if (count($targets) > 0) {
            // find Target Papers
            $papers = Paper::whereIn('category_id', $targets)->whereIn('id', $accept_papers)->get();
            $zipFN = 'files.zip';
            $zipFP = storage_path('app/' . $zipFN);
            $zip = new ZipArchive();
            if ($zip->open($zipFP, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($papers as $paper) {
                    $paper->addFilesToZip_ForPub($zip, $filetypes, $req->input("fn_prefix") . $paper->submits()->first()->booth);
                }
                $zip->close();

                // Zipアーカイブをダウンロード
                return response()->download($zipFP)->deleteFileAfterSend(true);
            } else {
                return response()->json(['message' => 'Zipファイルを作成できませんでした。'], 500);
            }
        }
        return response()->json(['message' => 'ここは実行されない。'], 500);
        // return view('admin.zipdownload')->with(compact("targets","filetypes"));
    }

    /**
     * 書誌情報の確認と修正
     */
    public function bibinfochk(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub')) abort(403);

        $subs = Submit::with('paper')->where("category_id", $catid)->where("accept_id", "<", 20)->orderBy("orderint")->get();

        return view('pub.bibinfochk', ["cat" => $catid])->with(compact("subs"));
    }

    /**
     * bibinfo for web (プログラム出力)
     */
    public function bibinfo(int $catid, bool $abbr = false)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub')) abort(403);

        $subs = Submit::with('paper')->where("category_id", $catid)->where("accept_id", "<", 20)->orderBy("orderint")->get();

        return view('pub.bibinfo', ["cat" => $catid])->with(compact("subs", "catid", "abbr"));
    }

    /**
     * 表彰状作成用のJSON
     * awards/json_booth_title_author/{key}
     * プログラム生成にも使えるように、affils を追加。
     */
    public function json_bta(string $key = null)
    {
        $downloadkey = Setting::findByIdOrName("AWARDJSON_DLKEY","value");
        if ($key != $downloadkey) abort(403);

        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        $out = [];
        foreach ($cats as $catid => $cname) {
            $subs = Submit::with('paper')->where("category_id", $catid)->where("accept_id", "<", 20)->orderBy("orderint")->get();
            foreach ($subs as $sub) {
                $booth = $sub->booth;
                //  $ary['title']
                //  $ary['authors'] = [ "著者1" , "著者2", ...]
                //  $ary['affils'] = [ 著者1の所属, 著者2の所属, ... ]
                $out[$booth] = $sub->paper->bibinfo(); // title=>xxx  authors = [xxx,xxx]  affils = [xxx,xxx]
            }
        }
        return json_encode($out, JSON_THROW_ON_ERROR);
    }
}
