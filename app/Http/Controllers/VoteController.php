<?php

namespace App\Http\Controllers;

use App\Exports\VoteAnswersExport;
use App\Http\Requests\StorevotesRequest;
use App\Http\Requests\UpdatevotesRequest;
use App\Models\Submit;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        if ($req->method() === 'POST') {
            // info($req->all());
            if (strlen($req->input("sssname")) < 3 ||   strlen($req->input("sssaffil")) < 2) {
                return redirect('/vote')->with('feedback.error', '氏名と所属の両方を入力してください。');
            }
            $formData = json_encode($req->all());
            $minutes = 60 * 24 * 3; // 3日間有効なクッキー
            Cookie::queue('formData', $formData, $minutes);
            return redirect('/vote')->with('feedback.success', '氏名と所属を一時保存しました。');
        }
        $formData = json_decode(Cookie::get('formData'), true);
        return view("vote.index")->with(compact("formData"));
    }
    public function vote(Request $req, Vote $vote)
    {
        if (Auth::check()) {
            $uid = auth()->id();
            $formData = ["uid" => $uid, "_token" => "uid_" . $uid];
        } else {
            $uid = null;
            $formData = json_decode(Cookie::get('formData'), true);
        }
        if (!$formData) return redirect('/vote')->with('feedback.error', '投票のまえに、氏名と所属を入力してください。');
        if (!$vote->isopen || $vote->isclose) {
            return redirect('/vote')->with('feedback.error', '期間外の投票はできません。');
        }
        if ($req->method() === 'POST') {
            if (!$vote->isopen || $vote->isclose) {
                return redirect('/vote')->with('feedback.error', '期間外の投票はできません。');
            }
            // info($req->all());
            // 一旦、これまでのデータをすべて消す。
            DB::transaction(function () use ($vote, $uid, $formData) {
                VoteAnswer::where("vote_id", $vote->id)->where(function ($query) use ($uid, $formData) {
                    $query->where("user_id", $uid)->orWhere("token", $formData['_token']);
                })->delete();
            });
            $subbooth2id = Submit::select("id", "booth")->get()->pluck("id", "booth")->toArray();
            // info($subbooth2id);
            $student_boothes = VoteItem::student_boothes();
            foreach ($req->all() as $booth => $val) {
                if ($val == 'on') {
                    VoteAnswer::firstOrCreate([
                        'user_id' => $uid,
                        'token' => $formData['_token'],
                        'submit_id' => $subbooth2id[$booth],
                        'valid' => (isset($student_boothes[$booth]) ? 2 : 1),
                    ], [
                        'comment' => $req->input('comment'),
                        'vote_id' => $vote->id,
                        'booth' => $booth,
                    ]);
                }
            }

            return redirect()->route('vote.vote', ['vote' => $vote])->with('feedback.success', '投票結果を保存しました。');
        }
        // チェック再現のため、保存データを取得する
        $vas = VoteAnswer::where("vote_id", $vote->id)->where(function ($query) use ($uid, $formData) {
            $query->where("user_id", $uid)->orWhere("token", $formData['_token']);
        })->get()->pluck("submit_id", "booth")->toArray();
        return view("vote.vote")->with(compact("vote", "formData", "uid", "vas"));
    }

    public function download_answers()
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);

        return Excel::download(new VoteAnswersExport(), "投票結果.xlsx");
    }

    /**
     * すべてリセットする
     */
    public function resetall($isclose = 0)
    {
        if (!auth()->user()->can('role_any', 'award')) abort(403);
        VoteAnswer::truncate();
        VoteItem::truncate();
        Vote::truncate();

        Vote::init($isclose);
        VoteItem::init();
        return redirect()->route('role.top', ['role' => 'award']);
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
    public function store(StorevotesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(votes $votes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(votes $votes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatevotesRequest $request, votes $votes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(votes $votes)
    {
        //
    }
}
