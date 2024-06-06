<?php

namespace App\Http\Controllers;

use App\Exports\EnqExportFromView;
use App\Http\Requests\StoreEnqueteRequest;
use App\Http\Requests\UpdateEnqueteRequest;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Http\Request;
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
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $enqs = Enquete::all();
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
        $papers = Paper::with('paperowner')->with('submits')->where('deleted', 0)->orderBy('category_id')->orderBy('id')->get();

        if ($req->has("action") && $req->input("action") == "excel") {
            return Excel::download(new EnqExportFromView($enq), "enqans_{$enq->name}.xlsx");
        }
        return view("enquete.answers")->with(compact("enq","enqans","papers"));

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

        return view("enquete.pageview")->with(compact("enq","enqs","enqans","paper"));
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

        return view("enquete.pageedit")->with(compact("enq","enqs","enqans","paper"));
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnqueteRequest $request, Enquete $enquete)
    {
        if ($request->ajax()) return $request->shori();
        else {
            // input type=numberでEnterをおすと、submitしてしまうので、ここでリダイレクトしてあげる
            return redirect()->route('enquete.pageedit',['paper'=>$request->input("paper_id"), 'enq'=>$request->input("enq_id")]);
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
}
