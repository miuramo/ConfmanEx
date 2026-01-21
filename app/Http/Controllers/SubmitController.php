<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoresubmitRequest;
use App\Http\Requests\UpdatesubmitRequest;
use App\Models\Accept;
use App\Models\Bb;
use App\Models\Category;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\Paper;
use App\Models\Review;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\Viewpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use STS\ZipStream\Facades\Zip;
use ZipArchive;

class SubmitController extends Controller
{
    

    

    

    

    

    

    



    /**
     * 出版担当またはプログラムチェアによる、プログラム編成とブース設定
     */
    public function booth(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);

        if ($req->method() === 'POST') {
            if ($req->has("json")) { // set session
                $ary = json_decode($req->input("json"), true);
                $num = 1;
                foreach ($ary as $sessionid => $presens) { // [0=>pid1, 1=>pid2, ...]
                    $in_session_num = 1;
                    foreach ($presens as $pid) {
                        $sub = Submit::where("category_id", $catid)->where("paper_id", $pid)->first();
                        $sub->psession_id = $sessionid;
                        $sub->orderint = $num;
                        $sub->save();
                        $num++;
                        $in_session_num++;
                    }
                }
                return "OK"; // json_encode($req->all());
            } else {
                if (!preg_match("/%[0-9]*d/", $req->input("print_format"))) return "ERROR: sprintfフォーマットを見直してください。" . $req->input("print_format");
                $subs = Submit::subs_accepted($catid);
                $num = 1 + $req->input("additional");
                $last_session_num = -1;
                $in_session_num = 1;
                foreach ($subs as $sub) {
                    if ($req->input("action") == "byorder") {
                        $sub->booth = sprintf($req->input("print_format"), $num);
                        $num++;
                    } else if ($req->input("action") == "bysession") {
                        $session_num = $sub->psession_id;
                        if ($last_session_num != $session_num) {
                            $in_session_num = 1;
                        }
                        $sub->booth = sprintf($req->input("print_format"), $session_num, $in_session_num);
                        $in_session_num++;
                        $last_session_num = $session_num;
                    }
                    $sub->save();
                }
                return redirect()->route('pub.booth', ["cat" => $catid]);
            }
        }

        $subs = Submit::subs_accepted($catid);
        // info($subs);
        return view('pub.booth', ["cat" => $catid])->with(compact("subs"));
    }

    public function boothtxt(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);

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
                    continue;
                    // return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.error', ($n + 1) . '行目付近にエラーがあります。要素は3つである必要があります。');
                }
                //
                if (!is_numeric($ary[0]) || !is_numeric($ary[1])) {
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
            foreach ($paper_session_map as $paperid => $sessionid) {
                $sub = Submit::where("category_id", $catid)->where("paper_id", $paperid)->first();
                if ($sub == null) continue;
                $sub->booth = $paper_booth_map[$paperid];
                $sub->psession_id = $sessionid;
                $sub->save();
            }
            // orderint を自動で更新
            $subs = Submit::subs_accepted($catid, "booth");
            $num = 1;
            foreach ($subs as $sub) {
                $sub->orderint = $num;
                $sub->save();
                $num++;
            }
            return redirect()->route('pub.boothtxt', ["cat" => $catid])->with("sbmap", $sbmap)->with('feedback.success', '割り当て実行しました。');
        }
        $subs = Submit::subs_accepted($catid);
        return view('pub.boothtxt', ["cat" => $catid])->with(compact("subs", "sbmap"));
    }

    /** 
     * ブースの割り当てを修正する
     */
    public function boothmodify(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web|demo')) abort(403);
        $tableName = 'submits';
        $coldetails = AdminController::column_details($tableName);
        $ary = ['paper_id', 'booth','orderint', 'psession_id'];
        $cold2 = [];
        foreach ($ary as $f) {
            if (isset($coldetails[$f])) $cold2[$f] = $coldetails[$f];
        }
        $coldetails = $cold2;
        $title = "ブース割り当ての修正（注：査読結果に紐づいているため、PaperIDの編集はしないでください）";

        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $whereBy['booth'] = $req->input("booth");
        $tableComments = AdminController::get_table_comments($db_name, $tableName);
        $data = DB::table($tableName)->whereIn("booth", explode("_", $req->input("booth")))
        ->orWhereIn("paper_id", explode("_", $req->input("pid")))->orderBy('orderint')->limit(10)->get()->toArray();
        $numdata = count($data);
        if ($req->input('action')=='swap' && $numdata == 2){
            // booth, orderint, psession_id をいれかえる
            $first = Submit::find($data[0]->id);
            $second = Submit::find($data[1]->id);
            $fields = ['booth', 'orderint', 'psession_id'];
            foreach($fields as $f){
                $tmp = $first->{$f};
                $first->{$f} = $second->{$f};
                $second->{$f} = $tmp;
            }
            $first->save();
            $second->save();
            return redirect()->route('pub.boothmodify', ['booth'=>$req->input("booth"), 'pid'=>$req->input('pid')])->with('feedback.success', 'ブースを入れ替えました。');
        }

        $back_link_href = route("role.top", ['role'=>'demo']);
        $back_link_label = "Topに戻る";
        return view('admin.crudtable2')->with(compact(
            "tableName",
            "coldetails",
            "data",
            "whereBy",
            "numdata",
            "tableComments",
            "title",
            "back_link_href",
            "back_link_label",
        ));
    }


    /**
     * ZIP file download for publication
     */
    public function zipdownload(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web|demo')) abort(403);
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        $filetypes = []; // pdf, video, img, altpdf
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
            if (strpos($k, "filetype") === 0) $filetypes[] = $v;
        }
        // 採択submits→paper_id list
        $accept_papers = Submit::with('paper')->whereIn("category_id", $targets)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderBy("orderint")->pluck("booth", "paper_id")->toArray();

        $addcount_tozip = 0;
        if (count($targets) > 0) {
            // find Target Papers
            $papers = Paper::whereIn('id', array_keys($accept_papers))->get();
            $zipFN = 'files.zip';
            $zipstream = Zip::create($zipFN);
            foreach ($papers as $paper) {
                if ($req->input('use_pid')) {
                    $paper->addFilesToZip_ForPub($zipstream, $filetypes, $req->input("fn_prefix"), sprintf("%03d", $paper->id));
                } else {
                    $fn = $accept_papers[$paper->id];
                    if (strlen($fn) < 1) $fn = sprintf("pid%03d", $paper->id);
                    $paper->addFilesToZip_ForPub($zipstream, $filetypes, $req->input("fn_prefix"), $fn);
                }
                $addcount_tozip++;
            }

            if ($addcount_tozip == 0) {
                return redirect()->route('role.top', ['role' => 'pub'])->with('feedback.error', 'まだ該当ファイルがないため、Zipファイルを作成できませんでした。');
            }
            // Zipアーカイブをダウンロード
            return $zipstream;
        }
        return response()->json(['message' => 'ここは実行されない。'], 500);
        // return view('admin.zipdownload')->with(compact("targets","filetypes"));
    }

    /**
     * 書誌情報の確認と修正
     */
    public function bibinfochk(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);

        $subs = Submit::subs_accepted($catid);
        // もし、subsが空なら、代替として、全てのsubmitsを表示する
        if (count($subs) == 0) {
            $subs2 = Submit::subs_all($catid);
        } else {
            $subs2 = [];
        }

        return view('pub.bibinfochk', ["cat" => $catid])->with(compact("subs", "subs2", "catid"));
    }
    public function bibinfochk_paper(Request $req, int $paperid)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);

        $sub = Submit::with('paper')->where("paper_id", $paperid)->first();
        // もし、subsが空なら、代替として、全てのsubmitsを表示する

        return view('pub.bibinfochk_paper')->with(compact("sub"));
    }

    /**
     * update maydirty (for reset) 確認済みにする (falseをセットする)
     */
    public function update_maydirty(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);
        info($req->all());
        $pid = $req->input("pid");
        $paper = Paper::findOrFail($pid);
        $field = $req->input("field");
        $value = $req->input("value"); // true or false
        $md = $paper->maydirty;
        if (isset($md[$field])) {
            $md[$field] = $value;
        }
        $paper->maydirty = $md;
        $paper->save();
        return json_encode(["maydirty" => $md]);
    }


    /**
     * bibinfo for web (プログラム出力)
     * useshort 所属を短縮するなら1 (preルールは0でも1でも適用される)
     * filechk 0 非表示、1 確認可能なリンク
     * postpone 0 通常、1 発表延期も表示する
     */
    public function bibinfo(int $catid, bool $abbr = false, int $useshort = 0, int $filechk = 0, int $postpone = 0)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|web')) abort(403);

        $subs = Submit::with('paper')->where("category_id", $catid)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderBy("orderint")->get();

        return view('pub.bibinfo', ["cat" => $catid])->with(compact("subs", "catid", "abbr", "useshort","filechk","postpone"));
    }

    /**
     * ファイルのタイムスタンプ確認（カメラレディ投稿されたか？）
     */
    public function fileinfochk(Request $req, int $catid)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|web')) abort(403);

        $subs = Submit::subs_accepted($catid);
        $pid2sub = [];
        foreach ($subs as $sub) {
            $pid2sub[$sub->paper->id] = $sub;
        }
        // $files = File::whereIn('paper_id', array_keys($pid2sub))->where('valid', 1)->where('deleted', 0)->get()->sortByDesc('created_at');
        $files = File::whereIn('paper_id', array_keys($pid2sub))->get()->sortByDesc('created_at');

        return view('pub.fileinfochk', ["cat" => $catid])->with(compact("pid2sub", "files"));
    }

    /**
     * 採択状況一覧
     */
    public function accstatus()
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|demo|web')) abort(403);
        $stats = Accept::acc_status();
        $paperlist = Accept::acc_status(true);
        $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $acc_judges = Accept::select('judge', 'id')->get()->pluck('judge', 'id')->toArray();
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        return view('pub.accstatus')->with(compact("stats", "accepts", "cats", "paperlist", "acc_judges"));
    }
    /**
     * 採択状況一覧（グラフ）
     */
    public function accstatusgraph()
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|demo|web')) abort(403);
        $stats = Accept::acc_status();
        $paperlist = Accept::acc_status(true);
        $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $acc_judges = Accept::select('judge', 'id')->get()->pluck('judge', 'id')->toArray();
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        return view('pub.accstatusgraph')->with(compact("stats", "accepts", "cats", "paperlist", "acc_judges"));
    }
    /**
     * 採択状況一覧・編集画面
     */
    public function accstatus_edit()
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|demo|web')) abort(403);
        return view('pub.accstatus_edit');
    }

    /**
     * 別カテゴリでの採否を追加する
     */
    public function addsubmit(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|web')) abort(403);
        if ($req->method() === 'POST') {
            // Submitsから、Paperをあつめていく
            $catid = $req->input("catid");
            $accid = $req->input("accid");
            $onlydemo = $req->input("onlydemo");
            $paperids_demo = Enquete::paperids_demoifaccepted($catid);
            $papers = Paper::whereHas("submits", function ($query) use ($catid, $accid, $onlydemo, $paperids_demo) {
                $query->where("category_id", $catid)->where("accept_id", $accid)->where("canceled", 0);
                if ($onlydemo) {
                    $query->whereIn("id", $paperids_demo);
                }
            })->get()->pluck('title', 'id')->toArray();

            if ($req->has("action") && $req->input("action") == "addsubmit") {
                $newcatid = $req->input("newcatid");
                $newaccid = $req->input("newaccid");
                foreach ($papers as $pid => $title) {
                    $sub = Submit::firstOrCreate([
                        'paper_id' => $pid,
                        'category_id' => $newcatid,
                    ], [
                        'accept_id' => $newaccid,
                    ]);
                    $sub->accept_id = $newaccid;
                    $sub->save();
                }
                return redirect()->route('pub.addsubmit')->with('feedback.success', '別カテゴリでの採否を追加しました。');
            }
            $checksubmit = true;
        } else {
            $papers = [];
            $checksubmit = false;
        }
        foreach (["catid", "accid", "newcatid", "newaccid"] as $k) {
            if (isset(${$k})) $old[$k] = ${$k};
            else $old[$k] = 1;
        }
        if (isset($onlydemo)) $old["onlydemo"] = $onlydemo;
        else $old["onlydemo"] = 0;
        $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        return view('pub.addsubmit')->with(compact("cats", "accepts", "papers", "old", "checksubmit"));

        // 現在の採択フラグ状況
        $fs = ["category_id", "accept_id", "name", "judge"];
        $sql1 = "select count(submits.id) as cnt, " . implode(",", $fs);
        $sql1 .= " from submits left join accepts on submits.accept_id = accepts.id where canceled = 0 group by " . implode(",", $fs);
        $sql1 .= " order by " . implode(",", $fs);
        $cols = DB::select($sql1);

        $sql2 = "select paper_id, category_id, accept_id ";
        $sql2 .= "from submits where canceled = 0 order by category_id, accept_id, paper_id";
        $res2 = DB::select($sql2);
        $pids = [];
        foreach ($res2 as $res) {
            if (is_array(@$pids[$res->category_id][$res->accept_id])) {
                $pids[$res->category_id][$res->accept_id][] = sprintf("%03d", $res->paper_id);
            } else {
                $pids[$res->category_id][$res->accept_id] = [];
                $pids[$res->category_id][$res->accept_id][] = sprintf("%03d", $res->paper_id);
            }
        }

        return view('pub.addsubmit')->with(compact("cats", "accepts", "papers", "old", "cols", "pids"));
    }

    /**
     * 表彰状作成用のJSON
     * awards/json_booth_title_author/{key}
     * プログラム生成にも使えるように、affils を追加。
     */
    public function json_bta(string $key = null, bool $readable = false, bool $use_short_for_affils = false, bool $use_short_for_bibauthors = false)
    {
        $downloadkey = Setting::getval("AWARDJSON_DLKEY");
        if ($key != $downloadkey) abort(403);

        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

        $enqans = EnqueteAnswer::getAnswers();

        $out = [];
        foreach ($cats as $catid => $cname) {
            $subs = Submit::subs_accepted($catid, "orderint");
            foreach ($subs as $sub) {
                $booth = $sub->booth;
                if (strlen($booth) == 0) $booth = sprintf("p%03d", $sub->paper->id);
                //  $ary['title']
                //  $ary['authors'] = [ "著者1" , "著者2", ...]
                //  $ary['affils'] = [ 著者1の所属, 著者2の所属, ... ]
                $out[$booth] = $sub->paper->bibinfo($use_short_for_affils); // title=>xxx  authors = [xxx,xxx]  affils = [xxx,xxx] $use_short=false
                $out[$booth]['session'] = $sub->psession_id;
                $out[$booth]['accept'] = $sub->accept_id;
                $out[$booth]['category'] = $sub->category_id;
                $out[$booth]['paperid'] = $sub->paper_id;
                $out[$booth]['abst'] = $sub->paper->abst;
                if ($sub->paper->pdf_file != null) {
                    $out[$booth]['pagenum'] = $sub->paper->pdf_file->pagenum;
                } else {
                    Log::channel("single")->info("json_bta: no pdf file for " . $sub->paper->id);
                    $out[$booth]['pagenum'] = '◆◆ no pdf◆◆';
                }
                $out[$booth]['bibauthors'] = $sub->paper->bibauthors(true, $use_short_for_bibauthors); //同一所属を省略 , use_short=true

                if (isset($enqans[$sub->paper_id])) {
                    foreach ($enqans[$sub->paper_id] as $enqid => $ary) {
                        foreach ($ary as $name => $val) {
                            $out[$booth][$name] = $val;
                        }
                    }
                }
            }
        }
        if ($readable) {
            return '<pre>' . htmlspecialchars(json_encode($out, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            return json_encode($out, JSON_THROW_ON_ERROR);
        }
    }

    /**
     * 【採録のみ】メタレビューのJSON
     * ただし、以下の条件をすべて満たす評価観点への回答のみ
     * formeta = 1
     * forrev = 0
     * doReturn = 1
     * doReturnAcceptOnly = 1
     */
    public function json_review(int $catid, string $key = null)
    {
        $downloadkey = Setting::getval("AWARDJSON_DLKEY");
        if ($key != $downloadkey) abort(403);

        // Viewpointで、formeta = 1 and doReturn = 1 のものを取得
        $vps = Viewpoint::select("name", "id")->where("formeta", 1)->where("forrev", 0)->where("doReturn", 1)->where("doReturnAcceptOnly", 1)->pluck("name", "id")->toArray();
        // 
        $accepted_subs = Submit::subs_accepted($catid);
        $revid2pid = Review::where("category_id", $catid)->where("ismeta", 1)->whereIn("paper_id", $accepted_subs->pluck("paper_id"))->pluck("paper_id", "id")->toArray();
        $scores = Score::whereIn("review_id", array_keys($revid2pid))->whereIn("viewpoint_id", array_keys($vps))->get();

        if (count($scores) == 0) {
            return json_encode([], JSON_THROW_ON_ERROR);
        }
        $out = [];
        foreach ($scores as $score) {
            $out[$vps[$score->viewpoint_id]][$revid2pid[$score->review_id]] = $score->valuestr;
        }
        // [ viewpoint1 => [ paper11 => コメント1, paper22 => コメント2, ... ] ]
        // return $out;
        return json_encode($out, JSON_THROW_ON_ERROR);
    }

    /**
     * ファイル情報のJSON
     * PIDごとに返す。
     */
    public function json_fileinfo(string $key = null, bool $readable = false)
    {
        $skip_unlink = true;
        $downloadkey = Setting::getval("AWARDJSON_DLKEY");
        if ($key != $downloadkey) abort(403);

        $out = [];
        $files = File::where('valid', 1)->where('deleted', 0)->get()->keyBy('id');
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        // $enqans = EnqueteAnswer::getAnswers();
        $out = [];
        foreach ($cats as $catid => $cname) {
            // if ($catid != 1) continue;
            $subs = Submit::subs_accepted($catid, "orderint");
            foreach ($subs as $sub) {
                $pid = $sub->paper->id;
                $booth = $sub->booth;
                $paper = $sub->paper;
                foreach ($paper->files as $file) {
                    $tmp = [];
                    $tmp['id'] = $file->id;
                    $tmp['fname'] = $file->fname;
                    $tmp['origname'] = $file->origname;
                    $tmp['key'] = $file->key;
                    $tmp['fullpath'] = $file->fullpath();
                    $fid = $file->id;
                    $tmp['ispdf'] = ($paper->pdf_file_id == $fid);
                    $tmp['isvideo'] = ($paper->video_file_id == $fid);
                    $tmp['isimg'] = ($paper->img_file_id == $fid);
                    $tmp['isaltpdf'] = ($paper->altpdf_file_id == $fid);
                    foreach (['ispdf', 'isvideo', 'isimg', 'isaltpdf'] as $k) {
                        if ($tmp[$k]) {
                            $tmp['filetype'] = substr($k, 2);
                            break;
                        }
                        $tmp['filetype'] = 'unlink_' . $file->id;
                    }
                    if ($skip_unlink && $tmp['filetype'] == 'unlink_' . $file->id) continue;
                    $tmp['mime'] = $file->mime;
                    $tmp['filesize'] = filesize($file->fullpath());
                    $tmp['pagenum'] = $file->pagenum;
                    if ($tmp['isimg']) $tmp['imagesize'] = getimagesize($file->fullpath());
                    $tmp['url'] = route('file.showhash', ['file' => $fid, 'hash' => substr($file->key, 0, 10)]);
                    $out[$pid][$tmp['filetype']] = $tmp;
                }
            }
        }
        if ($readable) {
            return '<pre>' . htmlspecialchars(json_encode($out, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            return json_encode($out, JSON_THROW_ON_ERROR);
        }
    }

    public function paperfile(int $paperid)
    {
        if (!auth()->user()->can('role_any', 'admin|pc|pub|web')) abort(403);
        $paper = Paper::findOrFail($paperid);
        $files = File::where('paper_id', $paperid)->orderBy('created_at', 'desc')->get();
        $bb = Bb::where('paper_id', $paperid)->where('type', 3)->first();
        return view('pub.paperfile')->with(compact("paper", "files", "bb"));
    }
}
