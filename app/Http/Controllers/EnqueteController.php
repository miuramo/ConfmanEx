<?php

namespace App\Http\Controllers;

use App\Exports\EnqExportFromView;
use App\Http\Requests\StoreEnqueteRequest;
use App\Http\Requests\UpdateEnqueteRequest;
use App\Models\Accept;
use App\Models\Category;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteConfig;
use App\Models\EnqueteItem;
use App\Models\Paper;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class EnqueteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        if (count($aEnq) < 1) abort(403);
        // if (!auth()->user()->can('role_any', 'pc|demo|acc')) abort(403);
        Enquete::reorderint(10); // orderint を再割り当てする
        $enqs = Enquete::accessibleEnquetes();
        return view("enquete.index")->with(compact("enqs"));
        //
    }
    /**
     * アンケート結果の表示またはダウンロード
     */
    public function answers(int $enq_id, Request $req)
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        if (!isset($aEnq[$enq_id])) abort(403);
        // if (!auth()->user()->can('role_any', 'pc')) abort(403);

        $enq = Enquete::find($enq_id);
        $enqans = EnqueteAnswer::where('enquete_id', $enq_id)->orderBy('paper_id')->get();

        // eans にふくまれる paper_id について、Paperをもってくる
        $papers = Paper::with('paperowner')->with('submits')->orderBy('category_id')->orderBy('id')->get();

        if ($req->has("action") && $req->input("action") == "excel") {
            return Excel::download(new EnqExportFromView($enq), "enqans_{$enq->name}.xlsx");
        }
        return view("enquete.answers")->with(compact("enq", "enqans", "papers"));
    }

    /**
     * アンケート回答の概要表示
     */
    public function anssummary(int $enq_id, Request $req)
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        if (!isset($aEnq[$enq_id])) abort(403);
        // if (!auth()->user()->can('role_any', 'pc')) abort(403);

        $enq = Enquete::find($enq_id);
        // 含まれるアンケート項目を取得
        $enqitems = EnqueteItem::where('enquete_id', $enq_id)->orderBy('orderint')->get();
        // アンケート項目ごとに、回答と件数を集計
        $sql1 = "select count(id) as cnt, valuestr, enquete_item_id from enquete_answers where enquete_id = {$enq_id} group by valuestr, enquete_item_id";
        $cols = DB::select($sql1);
        $res = [];
        foreach ($cols as $c) {
            $res[$c->enquete_item_id][$c->valuestr] = $c->cnt;
        }

        // さらに、カテゴリと、採択フラグで分類
        $sql2 = "select count(enquete_answers.id) as cnt, valuestr, enquete_item_id, category_id, accept_id " .
            "from enquete_answers " .
            "left join submits on enquete_answers.paper_id = submits.paper_id " .
            "where enquete_id = {$enq_id} and accept_id in (select id from accepts where judge > 0)" . 
            "group by valuestr, enquete_item_id, category_id, accept_id " .
            "order by valuestr, enquete_item_id, category_id, accept_id ";
        $cols2 = DB::select($sql2);
        $res2 = [];
        foreach ($cols2 as $c) {
            $res2[$c->enquete_item_id][$c->valuestr][$c->category_id][$c->accept_id] = $c->cnt;
        }
        // カテゴリのみで分類
        $sql2c = "select count(enquete_answers.id) as cnt, valuestr, enquete_item_id, category_id " .
            "from enquete_answers " .
            "left join submits on enquete_answers.paper_id = submits.paper_id " .
            "where enquete_id = {$enq_id} and accept_id in (select id from accepts where judge > 0)" . 
            "group by valuestr, enquete_item_id, category_id " .
            "order by valuestr, enquete_item_id, category_id ";
        $cols2c = DB::select($sql2c);
        $res2c = [];
        foreach ($cols2c as $c) {
            $res2c[$c->enquete_item_id][$c->valuestr][$c->category_id] = $c->cnt;
        }

        // PaperIDを羅列するために、group by をしない
        $sql3 = "select enquete_answers.paper_id, valuestr, enquete_item_id, category_id, accept_id " .
            "from enquete_answers " .
            "left join submits on enquete_answers.paper_id = submits.paper_id " .
            "where enquete_id = {$enq_id} and accept_id in (select id from accepts where judge > 0)" . 
            "order by valuestr, enquete_item_id, category_id, accept_id, enquete_answers.paper_id ";
        $cols3 = DB::select($sql3);
        $res3 = [];
        foreach ($cols3 as $c) {
            $res3[$c->enquete_item_id][$c->valuestr][$c->category_id][$c->accept_id][] = $c->paper_id;
        }

        // 未回答を、submit から、enquete_answers.paper_id にないものを取得
        foreach($enqitems as $ei){
            $sql4 = "select count(id) as cnt, category_id from submits ". 
            "where paper_id not in (select paper_id from enquete_answers where enquete_item_id = {$ei->id}) ".
            "and accept_id in (select id from accepts where judge > 0)" .
            "group by category_id ". 
            "order by category_id ";
            $cols4 = DB::select($sql4); 
            $noans_cat = [];
            foreach ($cols4 as $c) {
                $noans_cat[$c->category_id] = $c->cnt;
            }
        }


        $catlist = Category::select('id', 'shortname')->get()->pluck('shortname', 'id')->toArray();
        $acclist = Accept::select('id', 'shortname')->get()->pluck('shortname', 'id')->toArray();
        return view("enquete.anssummary")->with(compact("enq", "enqitems", "res", "res2", "res2c", "res3", "catlist", "acclist", "noans_cat"));
    }

    /**
     * アンケートの受付設定（期間、対象カテゴリ等）
     */
    public function config(int $enq_id, Request $req)
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        if (!isset($aEnq[$enq_id])) abort(403);
        if ($req->has('action')) {
            if ($req->input('action') == 'addrow') {
                $enq = Enquete::find($enq_id);
                $newdatum = new EnqueteConfig();
                $newdatum->enquete_id = $enq_id;
                $newdatum->save();
                return redirect()->route('enq.config', ["enq" => $enq_id])->with('feedback.success', '行を追加しました');
            } else {
                // 設定更新
                $ecid_idx = array_flip($req->input('id')); // enq config id => index
                $catcsv = $req->input('catcsv');
                $openstart = $req->input('openstart');
                $openend = $req->input('openend');
                $valid = $req->input('valid');
                $memo = $req->input('memo');
                $orderint = $req->input('orderint');
                foreach ($ecid_idx as $ecid => $idx) {
                    $enqconf = EnqueteConfig::find($ecid);
                    $enqconf->catcsv = $catcsv[$idx];
                    $enqconf->openstart = $openstart[$idx];
                    $enqconf->openend = $openend[$idx];
                    $enqconf->valid = $valid[$idx];
                    $enqconf->memo = $memo[$idx];
                    $enqconf->orderint = $orderint[$idx];
                    $enqconf->save();
                }
                return redirect()->route('enq.config', ["enq" => $enq_id])->with('feedback.success', '設定を更新しました');
            }
        }

        $configs = EnqueteConfig::where('enquete_id', $enq_id)->get();
        return view("enquete.config")->with(compact("configs", "enq_id"));
    }

    /**
     * アンケート項目編集用のCRUD 2
     */
    public function enqitmsetting(Request $req)
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        if (count($aEnq) < 1) abort(403);
        // info($aEnq);
        $enq_id = $req->input('enq_id');
        if (!isset($aEnq[$enq_id])) abort(403);
        // if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $tableName = 'enquete_items';
        // copy_id がセットされていたら、行をコピーする
        if ($req->has('copy_id')) {
            DB::transaction(function () use ($req) {
                $copy_id = $req->input('copy_id');
                $enqitm = EnqueteItem::find($copy_id);
                $newdatum = $enqitm->replicate(); // copy data
                $newdatum->orderint++;
                $newdatum->save();
            });
        }
        // del_id がセットされていたら、行を削除する
        if ($req->has('del_id')) {
            $del_id = $req->input('del_id');
            EnqueteItem::destroy($del_id);
        }
        $coldetails = AdminController::column_details($tableName);
        $coldetails['COPY'] = 'COPY';
        $ary = ['COPY', 'orderint', 'name', 'desc', 'content', 'contentafter', 'is_mandatory', 'pregrule', 'pregerrmes'];
        $cold2 = [];
        foreach ($ary as $f) {
            if (isset($coldetails[$f])) $cold2[$f] = $coldetails[$f];
        }
        $coldetails = $cold2;
        $title = "「" . $req->input('enq_name') . "」アンケート項目の編集";

        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $whereBy['enquete_id'] = $req->input("enq_id");
        $tableComments = AdminController::get_table_comments($db_name, $tableName);
        $data = DB::table($tableName)->where("enquete_id", $req->input("enq_id"))
            ->orderBy('orderint')->limit(100)->get()->toArray();
        $numdata = DB::table($tableName)->count();
        $back_link_href = route("enq.index");
        $back_link_label = "アンケート一覧に戻る";
        $enq_id = $req->input("enq_id");
        $enq_name = $req->input("enq_name");
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
            "enq_id",
            "enq_name"
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);

        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnqueteRequest $request)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);

        //
    }

    /**
     * 単一・独立ページでの表示
     */
    public function show(Paper $paper, Enquete $enq)
    {
        if (!Gate::allows('show_paper', $paper)) {
            abort(403, 'forbidden_for_others');
        }
        $enqs = Enquete::needForSubmit($paper);
        $eans = EnqueteAnswer::where('paper_id', $paper->id)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
        }

        return view("enquete.pageview")->with(compact("enq", "enqs", "enqans", "paper"));
        //
    }

    /**
     * 単一・独立ページでの編集
     */
    public function edit(Paper $paper, Enquete $enq)
    {
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        $enqs = Enquete::needForSubmit($paper);
        $eans = EnqueteAnswer::where('paper_id', $paper->id)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
        }

        return view("enquete.pageedit")->with(compact("enq", "enqs", "enqans", "paper"));
        //
    }

    // enq preview (for every user)
    public function edit_dummy(Enquete $enq, bool $foradmin = false)
    {
        //Paperアンケートのときは、Paperのカテゴリが要求するアンケート→それぞれの質問項目、の順に集めたが、プレビューなので後者のみ。
        // $aEnq = Enquete::accessibleEnquetes(true);
        // if (!isset($aEnq[$enq->id])) abort(403);
        $itms = EnqueteItem::where('enquete_id', $enq->id)->orderBy('orderint');
        $enqans = [];
        $enqs["canedit"][$enq->id] = $enq;
        $config = EnqueteConfig::where('enquete_id', $enq->id)->first();
        $enqs["until"][$enq->id] = Enquete::mm_dd_fancy($config->openend);
        $paper = new Paper();
        $paper->id = 0;
        $paper->category_id = 1;
        return view("enquete.pageedit")->with(compact("enq", "enqs", "enqans", "paper", "foradmin"));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnqueteRequest $request, Enquete $enquete)
    {
        if ($request->ajax()) return $request->shori();
        else {
            // input type=numberでEnterをおすと、submitしてしまうので、ここでリダイレクトしてあげる
            return redirect()->route('enquete.pageedit', ['paper' => $request->input("paper_id"), 'enq' => $request->input("enq_id")]);
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enquete $enquete)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);

        //
    }

    /**
     * アンケートの管理権限をRoleにわりあてる
     */
    public function map_to_roles(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|manager|admin')) abort(403);
        $roles = Role::all();
        $enqs = Enquete::all();
        $roleid_desc = Role::select("id", "desc")->pluck("desc", "id")->toArray();
        $enqid_name = Enquete::select("id", "name")->pluck("name", "id")->toArray();

        if ($req->method() === 'POST') {
            $all = $req->all();
            DB::table('enquete_roles')->truncate();
            foreach ($all as $name => $val) {
                if (strpos($name, "map_") === 0 && $val === 'on') {
                    $ary = explode("_", $name);
                    $enq = Enquete::find($ary[1]);
                    $enq->roles()->syncWithoutDetaching($ary[2]);
                }
            }
        }

        $row = DB::select("SELECT `role_id`,`enquete_id` FROM enquete_roles ");
        $enq_role_map = [];
        foreach ($row as $r) {
            $enq_role_map[$r->enquete_id][$r->role_id] = 1;
        }
        // info($enq_role_map);
        return view("enquete.maptoroles")->with(compact("roles", "enqs", "roleid_desc", "enqid_name", "enq_role_map"));
        //
    }

    /**
     * EnqueteAnswer を部分的に削除する
     */
    public function resetenqans(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|manager|admin')) abort(403);
        if ($req->method() === 'POST') {
            if ($req->has("action")) {
                foreach ($req->all() as $k => $v) {
                    if (strpos($k, "map_") === 0) {
                        $ary = explode("_", $k);
                        $enqId = $ary[1];
                        $catId = $ary[2];

                        EnqueteAnswer::where("enquete_id", $enqId)
                            ->whereHas("papers", function ($query) use ($catId) {
                                $query->where('papers.category_id', $catId);
                            })->delete();
                    }
                }

                // // Formからのカテゴリ選択を配列にいれる
                // $targets = [];
                // foreach ($req->all() as $k => $v) {
                //     if (strpos($k, "targetcat") === 0) $targets[] = $v;
                // }
                // if (count($targets) == 0) $targets =  [1, 2, 3];

                // $pidary = Paper::select('id')->whereIn('category_id', $targets)
                //     ->get()->pluck('title', 'id')->toArray();
                // $enqans = EnqueteAnswer::whereIn("paper_id", array_keys($pidary))->get();
                // foreach ($enqans as $enqa) {
                //     EnqueteAnswer::destroy($enqa->id);
                // }
            }
        }

        // 集約でカウント→cnt 
        $fs = ["enquete_answers.enquete_id", "papers.category_id"];
        $sql1 = "select count(enquete_answers.id) as cnt, " . implode(",", $fs);
        $sql1 .= " from enquete_answers left join papers on enquete_answers.paper_id = papers.id group by " . implode(",", $fs);
        $sql1 .= " order by " . implode(",", $fs);
        $cols = DB::select($sql1);
        $cnts = []; // enquete_id, category_id
        foreach ($cols as $c) {
            $cnts[$c->enquete_id][$c->category_id] = $c->cnt;
        }
        $enqs = Enquete::select('id', 'name')->get()->pluck("name", "id")->toArray("name", "id");
        $cats = Category::select('id', 'name')->get()->pluck("name", "id")->toArray("name", "id");
        return view("enquete.resetenqans")->with(compact("cnts", "enqs", "cats"));
    }
}
