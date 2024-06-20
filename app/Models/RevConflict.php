<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RevConflict extends Model
{
    use HasFactory;


    protected $fillable = [
        'paper_id',
        'user_id',
        'author_id',
        'bidding_id',
    ];

    public function bidding()
    {
        return $this->belongsTo(Bidding::class, 'bidding_id');
    }

    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = bidding_id
     */
    public static function arr_pu_bid()
    {
        $ret = [];
        foreach (RevConflict::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = $a->bidding_id;
        }
        return $ret;
    }
    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = bidding_name
     */
    public static function arr_pu_bname()
    {
        $bids = Bidding::pluck("name", "id")->toArray();
        $bidbgs = Bidding::pluck("bgcolor", "id")->toArray();
        $ret = [];
        foreach (RevConflict::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = "<span class=\"text-sm text-{$bidbgs[$a->bidding_id]}-500\">{$bids[$a->bidding_id]}</span>";
        }
        return $ret;
    }
    /**
     * Bidding未入力件数
     *
     *
     *
     * rev_conflicts には Category はない。user_id も抜けがある。
     *
     *     // PDFファイルがある投稿の数
App\Models\RevConflict::select(DB::raw("count(id) as count, user_id"))
  ->groupBy("user_id")
  ->orderBy("user_id")
  ->get()
  ->pluck("count", "user_id");
    field=id or name
     */
    public static function bidding_status($skip_finished = false, $field = "id")
    {
        // 現在、OpenしているBiddingについて (Category.bidding_on && !bidding_off)
        $catid = Category::where("status__bidding_on", true)->where("status__bidding_off", false)
            ->get()
            ->pluck('name', 'id')
            ->toArray();
        // reviewer
        $reviewers = Role::findByIdOrName('reviewer')->users;
        // 現在、BiddingをしているPaperIDs
        $missing = [];
        foreach ($reviewers as $reviewer) {
            // 当該Reviewerの入力済み
            $finished = RevConflict::where('user_id', $reviewer->id)
                ->get()->pluck("bidding_id", "paper_id")->toArray();

            $miss = Paper::whereIn("category_id", array_keys($catid))
                ->whereNotNull("pdf_file_id")
                ->whereNotIn('id', array_keys($finished))
                ->get()
                ->pluck("title", "id")
                ->toArray();
            if ($skip_finished && count($miss) == 0) continue;
            $missing[$reviewer->{$field}] = $miss;
        }
        return $missing;
    }
    /**
     * bidding_id でgroup by
     */
    public static function bidding_stat($catid)
    {
        $papers_in_cat = Category::find($catid)->paperswithpdf->pluck("title","id")->toArray();

        $tmp = RevConflict::select(DB::raw("count(id) as count, paper_id, bidding_id"))
            ->whereIn('paper_id', array_keys($papers_in_cat))
            ->groupBy("paper_id")
            ->groupBy("bidding_id")
            ->orderBy("paper_id")
            ->get();
        $ret = [];
        foreach ($tmp as $t) {
            $ret[$t->paper_id][$t->bidding_id] = $t->count;
        }
        return $ret;
    }

    /**
     * 申告利害に、現在のユーザの共著関係をまとめたもの
     */
    public static function arr_pu_rigai()
    {
        $ret = [];
        foreach (RevConflict::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = $a->bidding_id; // 1が利害by著者,2が利害by査読者
        }
        // ユーザ自身の共著論文をとりよせる
        $my_uid = auth()->id();
        $me = User::find($my_uid);
        foreach ($me->coauthor_papers() as $paper) {
            if (isset($ret[$paper->paper_id][$my_uid])) {
                $ret[$paper->paper_id][$my_uid] = 1;
            }
        }
        return $ret;
    }
}
