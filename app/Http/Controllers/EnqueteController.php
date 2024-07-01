<?php

namespace App\Http\Controllers;

use App\Exports\EnqExportFromView;
use App\Http\Requests\StoreEnqueteRequest;
use App\Http\Requests\UpdateEnqueteRequest;
use App\Models\Category;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
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
        if (!auth()->user()->can('role_any', 'pc|demo|acc')) abort(403);
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
        if (!auth()->user()->can('role_any', 'pc')) abort(403);

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
     * アンケート項目編集用のCRUD 2
     */
    public function enqitmsetting(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $tableName = 'enquete_items';
        // copy_id がセットされていたら、行をコピーする
        if ($req->has('copy_id')) {
            $copy_id = $req->input('copy_id');
            $enqitm = EnqueteItem::find($copy_id);
            $newdatum = $enqitm->replicate(); // copy data
            $newdatum->orderint++;
            $newdatum->save();
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

    public function edit_dummy(Enquete $enq)
    {
        //Paperアンケートのときは、Paperのカテゴリが要求するアンケート→それぞれの質問項目、の順に集めたが、プレビューなので後者のみ。
        if (!auth()->user()->can('role', 'pc')) return abort(403);
        $itms = EnqueteItem::where('enquete_id', $enq->id)->orderBy('orderint');
        $enqans = [];
        $enqs["canedit"][$enq->id] = $enq;
        $enqs["until"][$enq->id] = "(dummy)";
        $paper = new Paper();
        $paper->id = 0; 
        $paper->category_id = 1;
        return view("enquete.pageedit")->with(compact("enq", "enqs", "enqans", "paper"));
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
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $roles = Role::all();
        $enqs = Enquete::all();
        $roleid_desc = Role::select("id","desc")->pluck("desc","id")->toArray();
        $enqid_name = Enquete::select("id","name")->pluck("name","id")->toArray();

        if ($req->method()==='POST'){
            $all = $req->all();
            DB::table('enquete_roles')->truncate();
            foreach($all as $name=>$val){
                if (strpos($name,"map_")===0 && $val==='on'){
                    $ary = explode("_",$name);
                    $enq = Enquete::find($ary[1]);
                    $enq->roles()->attach($ary[2]);
                }
            }
        }

        $row = DB::select("SELECT `role_id`,`enquete_id` FROM enquete_roles ");
        $enq_role_map = [];
        foreach($row as $r){
            $enq_role_map[$r->enquete_id][$r->role_id] = 1;
        }
        // info($enq_role_map);
        return view("enquete.maptoroles")->with(compact("roles","enqs","roleid_desc","enqid_name","enq_role_map"));
        //
    }

    /**
     * EnqueteAnswer を部分的に削除する
     */
    public function resetenqans(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        if ($req->has("action")) {
            // Formからのカテゴリ選択を配列にいれる
            $targets = [];
            foreach ($req->all() as $k => $v) {
                if (strpos($k, "targetcat") === 0) $targets[] = $v;
            }
            if (count($targets) == 0) $targets =  [1, 2, 3];

            $pidary = Paper::select('id')->whereIn('category_id', $targets)
                ->get()->pluck('title', 'id')->toArray();
            $enqans = EnqueteAnswer::whereIn("paper_id", array_keys($pidary))->get();
            foreach ($enqans as $enqa) {
                EnqueteAnswer::destroy($enqa->id);
            }
        }
        return view("enquete.resetenqans");
    }
}
