<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoreRequest;
use App\Http\Requests\UpdateScoreRequest;
use App\Models\Category;
use App\Models\Review;
use App\Models\Score;
use App\Models\Viewpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    

    

    

    

    

    

    

    /**
     * Score を部分的に削除する
     */
    public function resetscore(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|manager|admin')) abort(403);
        if ($req->method() === 'POST') {
            if ($req->has("action")) {
                foreach ($req->all() as $k => $v) {
                    if (strpos($k, "map_") === 0) {
                        $ary = explode("_", $k);
                        $vpId = $ary[1];
                        $catId = $ary[2];

                        Score::where("viewpoint_id", $vpId)
                            ->whereHas("review", function ($query) use ($catId) {
                                $query->where('reviews.category_id', $catId);
                            })->delete();
                    }
                }
                // すべての査読Reviewについて、validateOneRev()で状況を更新する。
                Review::validateAllRev();
            }
        }

        // 集約でカウント→cnt 
        $fs = ["scores.viewpoint_id", "reviews.category_id"];
        $sql1 = "select count(scores.id) as cnt, " . implode(",", $fs);
        $sql1 .= " from scores left join reviews on scores.review_id = reviews.id group by " . implode(",", $fs);
        $sql1 .= " order by " . implode(",", $fs);
        $cols = DB::select($sql1);
        $cnts = []; // enquete_id, category_id
        foreach ($cols as $c) {
            $cnts[$c->viewpoint_id][$c->category_id] = $c->cnt;
        }
        $vps = Viewpoint::select('id', 'desc')->orderBy("category_id")->orderBy("orderint")->get()->pluck("desc", "id")->toArray("desc", "id");
        $cats = Category::select('id', 'name')->get()->pluck("name", "id")->toArray("name", "id");
        return view("score.resetscore")->with(compact("cnts", "vps", "cats"));
    }
}
