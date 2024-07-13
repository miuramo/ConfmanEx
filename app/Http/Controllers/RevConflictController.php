<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRevConflictRequest;
use App\Http\Requests\UpdateRevConflictRequest;
use App\Models\Category;
use App\Models\MailTemplate;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RevConflictController extends Controller
{
    /**
     *
     * Bidding未完了状態を確認
     *
     */
    public function index()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $missing = RevConflict::bidding_status(false, "name"); // include all finished reviewer, key is name
        return view('revcon.index')->with(compact("missing"));
    }

    /**
     * 希望の数
     * 自分が利害じゃないものだけ見える
     */
    public function stat()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

        foreach ($cats as $cid => $cname) {
            $papers_in_cat[$cid] = Category::find($cid)->paperswithpdf->pluck("title", "id")->toArray();
            $counts[$cid] = RevConflict::bidding_stat($cid);
        }
        return view('revcon.stat')->with(compact("papers_in_cat", "counts", "cats"));
    }
    /**
     * 査読割り当て Review のまとめ
     */
    public function revstat()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

        foreach ($cats as $cid => $cname) {
            $papers_in_cat[$cid] = Category::find($cid)->paperswithpdf->pluck("title", "id")->toArray();
            $cnt_users[$cid] = Review::revass_stat($cid, "user_id");
            $cnt_papers[$cid] = Review::revass_stat($cid, "paper_id");
        }
        $reviewers = Role::findByIdOrName('reviewer')->users;
        $cnt_users_all = Review::revass_stat_allcategory();
        return view('revcon.revstat')->with(compact("papers_in_cat", "cnt_users", "cnt_papers", "cats", "reviewers", "cnt_users_all"));
    }
    /**
     * 査読進捗
     */
    public function revstatus()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        Review::validateAllRev(); // statusを更新
        
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        $sum_cm_tmp = Review::select(DB::raw("count(id) as count, category_id, ismeta"))
            ->groupBy("category_id")
            ->groupBy("ismeta")
            ->get();
        $sum_cm = [];
        foreach ($sum_cm_tmp as $t) {
            $sum_cm[$t->category_id][$t->ismeta] = $t->count;
        }

        $cmsc = Review::select(DB::raw("count(id) as count, category_id,ismeta, status"))
            ->groupBy("category_id")
            ->groupBy("ismeta")
            ->groupBy("status")
            ->orderBy("category_id")
            ->orderBy("ismeta")
            ->orderByDesc("status")
            ->get();
        $us = Review::select(DB::raw("count(id) as count, user_id, status"))
            ->groupBy("user_id")
            ->groupBy("status")
            ->orderBy("user_id")
            ->orderByDesc("status")
            ->get();
        $usary = [];
        foreach($us as $t){
            $usary[$t->user_id][$t->status] = $t->count;
        }
        $revusers = [];
        foreach(Role::findByIdOrName('reviewer')->users as $u){
            $revusers[$u->id] = $u->name;
        }

        // notdownloaded
        foreach ($cats as $catid => $cname) {
            $nd[$catid] = MailTemplate::mt_notdownloaded($catid)->toArray();
        }
        return view('revcon.revstatus')->with(compact("cats", "cmsc", "sum_cm", "nd","revusers","us","usary"));
    }

    public function notdownloaded()
    {
    }
    public function norev()
    {
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
    public function store(StoreRevConflictRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RevConflict $revConflict)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RevConflict $revConflict)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRevConflictRequest $request, RevConflict $revConflict)
    {
        return $request->shori();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RevConflict $revConflict)
    {
        //
    }
}
