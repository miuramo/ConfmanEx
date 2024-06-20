<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRevConflictRequest;
use App\Http\Requests\UpdateRevConflictRequest;
use App\Models\Category;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;

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
        $missing = RevConflict::bidding_status(false,"name"); // include all finished reviewer, key is name
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

        foreach($cats as $cid=>$cname){
            $papers_in_cat[$cid] = Category::find($cid)->paperswithpdf->pluck("title","id")->toArray();
            $counts[$cid] = RevConflict::bidding_stat($cid);
        }
        return view('revcon.stat')->with(compact("papers_in_cat","counts","cats"));
    }
    /**
     * 査読割り当て Review のまとめ
     */
    public function revstat()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

        foreach($cats as $cid=>$cname){
            $papers_in_cat[$cid] = Category::find($cid)->paperswithpdf->pluck("title","id")->toArray();
            $cnt_users[$cid] = Review::revass_stat($cid,"user_id");
            $cnt_papers[$cid] = Review::revass_stat($cid,"paper_id");
        }
        $reviewers = Role::findByIdOrName('reviewer')->users;
        $cnt_users_all = Review::revass_stat_allcategory();
        return view('revcon.revstat')->with(compact("papers_in_cat","cnt_users","cnt_papers","cats","reviewers","cnt_users_all"));
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
