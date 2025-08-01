<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_id',
        'name',
        'desc',
        'show_pdf_link',
        'orderint',
        'submits',
        'upperlimit',
    ];


    public function vote()
    {
        return $this->belongsTo(Vote::class);
    }

    public function answers()
    {
        return $this->hasMany(VoteAnswer::class);
    }

    /**
     * boothが決まらないと、投票アイテムは表示されないことに注意。
     */
    public static function init()
    {
        // 各カテゴリで、学生発表とそれ以外（一般発表）に分ける。
        // アンケートは4番、paper_id => valuestr をとっておく。
        // または、valuestr = 学生 のpaper_id 配列をとっておく。
        $student_pids = EnqueteAnswer::where("enquete_id", 4)->where("valuestr", "学生")->orderBy("paper_id")
            ->get()->pluck("paper_id")->toArray();
        // info($student_pids);

        // 1,2のカテゴリで、学生発表とそれ以外（一般発表）に分ける。
        /*        foreach([1,2] as $catid){
            foreach(["一般"=>false,"学生"=>true] as $zoku=>$val){
                $subs = Submit::where("category_id", $catid)->whereHas("accept", function($query) {
                    $query->where("judge", ">", 0);
                })->whereHas("paper", function($query) use ($student_pids, $val){
                    if ($val) $query->whereIn("id", $student_pids);
                    else $query->whereNotIn("id", $student_pids);
                })->orderBy("orderint")->select("paper_id","booth")->pluck("paper_id","booth")->toArray();
                // info($catid." ".$zoku);
                // info($subs);
                VoteItem::firstOrCreate(
                    [
                        'vote_id' => $catid,
                        'name' => "【{$zoku}発表】",
                    ],
                    [
                        'orderint' => ($val)? 2:1 ,
                        'submits' => json_encode($subs),
                    ]
                );

            }
        }
            */
        // 学生と一般を分けない
        $cats = Category::whereIn("id", [1, 2])->orderBy("id")->pluck("name", "id")->toArray();
        foreach ($cats as $catid => $catname) {
            $subs = Submit::where("category_id", $catid)->whereHas("accept", function ($query) {
                $query->where("judge", ">", 0);
            })->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();
            VoteItem::firstOrCreate(
                [
                    'vote_id' => $catid,
                    'name' => "【{$catname}】",
                ],
                [
                    'desc' => "素晴らしいとお感じになった発表",
                    'orderint' => 1,
                    'submits' => json_encode($subs),
                    'upperlimit' => 10, // 上限10
                ]
            );
        }

        // 論文賞 平均スコア2.3以上のものを対象とする。
        // $subs = Submit::where("category_id", 1)->whereHas("accept", function ($query) {
        //     $query->where("judge", ">", 0);
        // })->where("score", ">=", 2.9)->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();

        $subs2 = Submit::where("category_id", 1)->whereIn("id", [7, 15, 35, 36, 10, 16]) // PaperIDで抽出する
            ->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();

        VoteItem::firstOrCreate(
            [
                'vote_id' => 3,
                'name' => "【論文賞】",
                'submits' => json_encode($subs2),
                'desc' => "論文賞に相応しい優れた論文",
            ],
            [
                'orderint' => 1,
                'upperlimit' => 1, // 上限1
                'show_pdf_link' => true, // 論文賞はPDFリンクを表示する
            ]
        );
    }

    // 学生発表のブースを取得
    // booth => paper_id の配列を返す。
    // 1,2のカテゴリで、学生発表とそれ以外（一般発表）に分ける。
    // ただし、アンケートは4番、paper_id => valuestr をとっておく。
    public static function init_boothes()
    {
        self::init();
    }
    public static function student_boothes()
    {
        $student_pids = EnqueteAnswer::where("enquete_id", 4)->where("valuestr", "学生")->orderBy("paper_id")
            ->get()->pluck("paper_id")->toArray();
        $subs = Submit::whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->whereHas("paper", function ($query) use ($student_pids) {
            $query->whereIn("id", $student_pids);
        })->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();
        return $subs;
    }
}
