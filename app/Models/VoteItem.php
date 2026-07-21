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
    public static function init(): void
    {
        $votes = Vote::where("valid", true)->get();
        foreach ($votes as $vote) {
            if ($vote->category_id === null) continue; // カテゴリIDがない場合はスキップ
            if ($vote->separate_student) {
                // 学生発表と一般発表を分ける場合
                $student_pids = explode(",", $vote->student_paper_ids);
                $cat_id = $vote->category_id;
                foreach (["一般" => false, "学生" => true] as $zoku => $val) {
                    $subs = Submit::where("category_id", $cat_id)->whereHas("accept", function ($query) {
                        $query->where("judge", ">", 0);
                    })->whereHas("paper", function ($query) use ($student_pids, $val) {
                        if ($val) $query->whereIn("id", $student_pids);
                        else $query->whereNotIn("id", $student_pids);
                    })->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();
                    VoteItem::firstOrCreate(
                        [
                            'vote_id' => $vote->id,
                            'name' => "【{$vote->name}：{$zoku}】",
                        ],
                        [
                            'orderint' => ($val) ? 2 : 1,
                            'submits' => json_encode($subs),
                            'upperlimit' => count($subs) * $vote->percentage_upperlimit, // 上限は、投票対象の件数に対して割合で設定する。
                        ]
                    );
                }
            } else {
                // 学生発表と一般発表を分けない場合
                $cat_id = $vote->category_id;
                $subs = Submit::where("category_id", $cat_id)->whereHas("accept", function ($query) {
                    $query->where("judge", ">", 0);
                })->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();
                $catname = Category::find($cat_id)->name ?? "不明";
                VoteItem::firstOrCreate(
                    [
                        'vote_id' => $vote->id,
                        'name' => "【{$catname}】",
                    ],
                    [
                        'desc' => "素晴らしいとお感じになった発表",
                        'orderint' => 1,
                        'submits' => json_encode($subs),
                        'upperlimit' => count($subs) * $vote->percentage_upperlimit, // 上限は、投票対象の件数に対して割合で設定する。
                    ]
                );
            }
        }

        // 論文賞 平均スコア4.0以上のものを対象とする。
        $subs = Submit::where("category_id", 1)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->where("score", ">=", 4.0)->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();

        // $subs2 = Submit::where("category_id", 1)->whereIn("id", [7, 15, 35, 36, 10, 16]) // PaperIDで抽出する
        //     ->orderBy("orderint")->select("paper_id", "booth")->pluck("paper_id", "booth")->toArray();
        foreach ($votes as $vote) {
            if ($vote->category_id !== null) continue; // カテゴリIDがない場合はスキップ

            VoteItem::firstOrCreate(
                [
                    'vote_id' => $vote->id,
                    'name' => "【{$vote->name}】",
                    'desc' => "{$vote->name}に相応しい優れた論文",
                ],
                [
                    'submits' => json_encode($subs),
                    'orderint' => 1,
                    'upperlimit' => 2, // 上限2
                    'show_pdf_link' => true, // 論文賞はPDFリンクを表示する
                ]
            );
        }
    }

    // 学生発表のブースを取得
    // booth => paper_id の配列を返す。
    // 1,2のカテゴリで、学生発表とそれ以外（一般発表）に分ける。
    // ただし、アンケートは4番、paper_id => valuestr をとっておく。
    public static function init_boothes(): void
    {
        self::init();
    }
    public static function student_boothes(): array
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
