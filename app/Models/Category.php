<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'leadtext',
    ];

    /**
     * PDFファイルがないものも含めて返す。
     */
    public function papers()
    {
        return $this->hasMany(Paper::class, 'category_id')->orderBy('id');
    }
    /**
     * PDFファイルがあるものだけを返す。
     */
    public function paperswithpdf()
    {
        return $this->hasMany(Paper::class, 'category_id')->whereNotNull('pdf_file_id')->orderBy('id');
    }

    public static function spans()
    {
        $all = Category::all();
        $spans = [];
        foreach ($all as $c) {
            $spans[$c->id] = "<span class=\"inline-block text-{$c->color}-500 bg-{$c->bgcolor}-200 text-md p-2 rounded-xl font-bold  dark:bg-{$c->color}-500 dark:text-{$c->bgcolor}-200\">{$c->name}</span>";
        }
        return $spans;
    }

    /**
     * 新規投稿受付ボタン
     */
    public function isOpen()
    {
        return Enquete::checkdayduration($this->openstart, $this->openend);
    }

    public function is_accept_pdf()
    {
        return Enquete::checkdayduration($this->pdf_accept_start, $this->pdf_accept_end);
    }

    /**
     * 投稿数が設定の上限(upperlimit)を超えたらfalse
     */
    public function isnotUpperLimit()
    {
        if ($this->upperlimit == 0) return true;
        $papercount = Paper::where("category_id", $this->id)->count();
        return ($papercount < $this->upperlimit);
    }

    /**
     * 査読結果を表示するかどうか
     * @param int $cat_id カテゴリID
     * ここの結果がtrue の場合、査読者やメタ査読者が、査読結果一覧をみれるようになる。（リンクが表示される。）
     */
    public static function isShowReview($cat_id)
    {
        $canshow = false;
        $revlist = Category::select('id', 'status__revlist_on')->get()->pluck('status__revlist_on', 'id')->toArray();
        $revlistfor = Category::select('id', 'status__revlist_for')->get()->pluck('status__revlist_for', 'id')->toArray();
        if (!auth()->user()->can('role', 'pc')) {
            if (auth()->user()->can('role_any', $revlistfor[$cat_id]) && $revlist[$cat_id]) {
                $canshow = true;
            } else {
            }
        } else {
            $canshow = true;
        }
        return $canshow;
    }

    /**
     * used at ReviewController.conflict
     */
    public static function canBid(int $cat_id)
    {
        $canbid = false;
        $bidding_on = Category::select('id', 'status__bidding_on')->get()->pluck('status__bidding_on', 'id')->toArray();
        $bidding_off = Category::select('id', 'status__bidding_off')->get()->pluck('status__bidding_off', 'id')->toArray();
        if (!auth()->user()->can('role', 'pc')) {
            if (auth()->user()->can('role_any', 'reviewer|metareviewer') && $bidding_on[$cat_id] && !$bidding_off[$cat_id]) {
                $canbid = true;
            }
        } else {
            $canbid = true;
        }
        return $canbid;
    }

    /**
     * PC長ではなく、manage_cat 権限のみの場合は、そのカテゴリのみ返す。
     */
    public static function manage_cats()
    {
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        if (auth()->user()->can('role', 'pc')) {
            return $cats;
        } else {
            if (auth()->user()->can('manage_cat_any')) {
                $res = DB::select("select distinct cat_id from roles where cat_id > 0 and "
                    . " id in (select role_id from role_user where user_id = ?)", [auth()->user()->id]);
                // どのcat を残すか？
                $catids = array_column($res, 'cat_id');
                foreach ($cats as $cid => $name) {
                    if (!in_array($cid, $catids) && isset($cats[$cid])) unset($cats[$cid]);
                }
                return $cats;
            }
        }
        return [];
    }
}
