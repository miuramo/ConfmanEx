<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBbRequest;
use App\Http\Requests\UpdateBbRequest;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use Illuminate\Http\Request;

class BbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        for ($i = 1; $i < 4; $i++) {
            $bbs[$i] = Bb::with("paper")->with("category")->where("type", $i)->get();
        }
        return view("bb.index")->with(compact("bbs"));
        //
    }

    public function index_for_pub()
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);

        $i = 3;
        $bbs[$i] = Bb::with("paper")->with("category")->where("type", $i)->get();

        return view("bb.index_for_pub")->with(compact("bbs"));
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
    public function store(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        $catid = $req->input("catid");
        $type = $req->input("type");
        $pids = trim($req->input("pids"));
        if ($pids == "all") {
            $ary = MailTemplate::mt_category($catid); // return: array of paperobj
        } else if ($pids == "accepted") {
            $ary = MailTemplate::mt_accept($catid); // return: array of paperobj
        } else {
            $ary = Paper::whereIn('id', explode(",", $pids))->get();
        }
        foreach ($ary as $n => $paper) {
            Bb::make_bb($type, $paper->id, $catid);
        }
        // 出版担当からの作成のとき 1
        $for_pub = $req->input("for_pub");
        if ($for_pub){
            return redirect()->route('bb.index_for_pub')->with('feedback.success', "作成しました。");
        }
        return redirect()->route('bb.index')->with('feedback.success', "作成しました。");
    }

    /**
     * Display the specified resource.
     */
    public function show(int $bbid, string $key)
    {
        $bb = Bb::with("messages")->with("paper")->with("category")->where('id', $bbid)->where('key', $key)->first();
        if ($bb == null) abort(403, 'bb not found');
        // type=1(査読掲示板) のとき、ユーザのrevid をセット
        if ($bb->type == 1) {
            $rev = Review::where("paper_id", $bb->paper_id)->where("category_id", $bb->category_id)->where("user_id", auth()->id())->first();
            if ($rev == null) $revid = null;
            else $revid = $rev->id;
            // 利害関係者は掲示板を見れないようにする
            $rigais = RevConflict::arr_pu_rigai();
            // if (!isset($rigais[$bb->paper->id][auth()->id()])) {
            //     return abort(403, 'missing rigai data (no bidding to the paper?)');
            // }
            if (isset($rigais[$bb->paper->id][auth()->id()]) && $rigais[$bb->paper->id][auth()->id()] < 3) {
                return abort(403, 'authors conflict');
            }
        } else {
            $revid = null;
        }
        return view("bb.show")->with(compact("bb", "revid"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bb $bb)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBbRequest $request, Bb $bb)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bb $bb)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        Bb::truncate();
        BbMes::truncate();
        return redirect()->route('bb.index')->with('feedback.success', "全削除しました。");
    }

    /**
     * 種別ごとに削除
     */
    public function destroy_bytype(Request $req){
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);
        $type = $req->input("type");
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) {
            if ($type != 3) abort(403);
        }
        $target_bbids = Bb::where("type", $type)->pluck("id");
        BbMes::whereIn("bb_id", $target_bbids)->delete();
        Bb::where("type", $type)->delete();
        $for_pub = $req->input("for_pub");
        if ($for_pub){
            return redirect()->route('bb.index_for_pub')->with('feedback.success', "出版掲示板をすべて削除しました。");
        }
        return redirect()->route('bb.index')->with('feedback.success', "削除しました。");
    }
}
