<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EnqueteAnswer;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'isclose',
        'separate_student',
        'for_pc',
        'student_paper_ids',
    ];

    public function items()
    {
        return $this->hasMany(VoteItem::class, 'vote_id');
    }


    public static function init($isclose = 0): void
    {
        $student_pids = EnqueteAnswer::where("enquete_id", 4)->where("valuestr", "学生")->orderBy("paper_id")
            ->get()->pluck("paper_id")->toArray();


        $categories = Category::all();
        foreach ($categories as $category) {
            if ($category->name == "予備") continue; // 予備カテゴリは除外する
            Vote::firstOrCreate(
                [
                    'name' => $category->name,
                    'category_id' => $category->id,
                ],
                [
                    'separate_student' => false, // デフォルトでは学生発表と一般発表を分けない
                    'isclose' => $isclose,
                    'student_paper_ids' => implode(",", $student_pids),
                ]
            );
        }
        Vote::firstOrCreate(
            [
                'name' => '論文賞',
            ],
            [
                'separate_student' => false, // デフォルトでは学生発表と一般発表を分けない
                'isclose' => $isclose,
                'student_paper_ids' => implode(",", $student_pids),
                'for_pc' => 1, // 論文賞はPC投票用フラグを立てる
            ]
        );
    }
}
