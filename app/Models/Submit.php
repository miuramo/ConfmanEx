<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submit extends Model
{
    use HasFactory;

    protected $with = ['accept'];
    protected $fillable = [
        'psessionid',
        'booth',
        'canceled',
        'award',
        'orderint',
        'accept_id',
        'category_id',
        'paper_id',
    ];

    public function accept()
    {
        return $this->belongsTo(Accept::class); //逆はbelongsTo
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, "submit_id")->orderBy('ismeta', 'desc');
    }

    /**
     * この査読のトークンを生成（査読者同士の参照用）
     */
    public function token()
    {
        return sha1($this->id . $this->paper_id . $this->category_id . $this->created_at);
    }

    public static function subs_accepted(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderBy($ord)->get();
        return $subs;
    }
    public static function subs_all(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereNot("paper_id", 0)->orderBy($ord)->get();
        return $subs;
    }

    /**
     * このSubmitに関連するReviewの点数を更新する
     */
    public function updateScoreStat()
    {
        // まず、このSubmitに関連するReviewを取得
        // $scores = Score::whereHas('viewpoint', function ($query) {
        //     $query->where('weight', '>', 0);
        // })->whereIn('review_id', $this->reviews->pluck('id'))->pluck('value')->toArray();
        // $sum = array_sum($scores);
        // まず、このSubmitに関連するReviewを取得
        $scores = Score::whereHas('viewpoint', function ($query) {
            $query->where('weight', '>', 0);
        })->whereIn('review_id', $this->reviews->pluck('id'))->get();

        // 同一ユーザごとにスコアを足し合わせる
        $userScores = [];
        foreach ($scores as $score) {
            $userId = $score->review->user_id;
            if (!isset($userScores[$userId])) {
                $userScores[$userId] = 0;
            }
            $userScores[$userId] += $score->value * $score->viewpoint->weight;
        }

        $weightedScores = array_values($userScores);
        $sum = array_sum($weightedScores);
        if (count($weightedScores) > 0) {
            $mean = $sum / count($weightedScores);
            $this->score = $mean;
            $this->stddevscore = sqrt(array_sum(array_map(function ($value) use ($mean) {
                return pow($value - $mean, 2);
            }, $weightedScores)) / count($weightedScores));
        } else {
            $this->score = null;
            $this->stddevscore = null;
        }
        $this->save();
    }

    /**
     * すべてのSubmitの点数統計(score, stddevscore)を更新する
     */
    public static function updateAllScoreStat()
    {
        $subs = Submit::all();
        foreach ($subs as $sub) {
            $sub->updateScoreStat();
        }
    }
}
