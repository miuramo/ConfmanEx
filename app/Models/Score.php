<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'viewpoint_id',
        'user_id',
        'value',
        'valuestr',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
    public function viewpoint()
    {
        return $this->belongsTo(Viewpoint::class);
    }

    public function submit_score_update()
    {
        // 点数に関係なければ終了
        if ($this->viewpoint->weight < 1) {
            $this->review->validateOneRev();
            return;
        }
        if ($this->review == null) return;
        // 対応するSubmitは、review_id -> Review
        $sub_id = $this->review->submit_id;
        //すべてのsub_id をもつReview idを探索 TODO: 会議によってはメタの点数を含めるかも。
        // $other_reviews = Review::where('submit_id', $sub_id)->where('ismeta', 0)->pluck('id');
        $other_reviews = Review::where('submit_id', $sub_id)->pluck('id');
        // $other_reviews[] = $this->review->id; // 自分をいれてしまうと、メタがふくまれてしまうことがある

        $vptargets = Viewpoint::where("weight", 1)->pluck("id")->toArray();
        $scores = Score::whereIn('review_id', $other_reviews)->whereIn('viewpoint_id', $vptargets)->pluck('value')->toArray();
        // $scores = Score::whereIn('review_id', $other_reviews)->whereHas('viewpoint', function ($query) {
        //     $query->where('weight', 1);
        // })->pluck('value')->toArray();

        $sum = array_sum($scores);
        if (count($scores) > 0) {
            $mean = $sum / count($scores);
            // 各値と平均値の差を2乗する
            $differencesSquared = array_map(function ($value) use ($mean) {
                return pow($value - $mean, 2);
            }, $scores);
            // 差の2乗の合計を計算
            $sumOfDifferencesSquared = array_sum($differencesSquared);
            // 分散を計算
            $variance = $sumOfDifferencesSquared / count($scores);
            // 標準偏差を計算
            $standardDeviation = sqrt($variance);
        } else {
            $mean = 0;
            $standardDeviation = 0;
        }

        // $stddev = Score::whereIn('review_id', $other_reviews)->selectRaw('STDDEV(value)')->value('STDDEV(value)');
        // $avg = Score::whereIn('review_id', $other_reviews)->selectRaw('AVERAGE(value)')->value('AVERAGE(value)');
        DB::transaction(function () use ($sub_id, $mean, $standardDeviation) {
            $sub = Submit::find($sub_id);
            $sub->score = $mean;
            $sub->stddevscore = $standardDeviation;
            $sub->save();
        });
    }

    public static function updateAllScoreStat()
    {
        $all = Score::whereHas('viewpoint', function ($query) {
            $query->where('weight', 1);
        })->get();
        foreach ($all as $sc) {
            $sc->submit_score_update();
        }
    }
}
