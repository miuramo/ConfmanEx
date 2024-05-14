<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        foreach(RevConflict::all() as $a){
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
        $bids = Bidding::pluck("name","id")->toArray();
        $bidbgs = Bidding::pluck("bgcolor","id")->toArray();
        $ret = [];
        foreach(RevConflict::all() as $a){
            $ret[$a->paper_id][$a->user_id] = "<span class=\"text-sm text-{$bidbgs[$a->bidding_id]}-500\">{$bids[$a->bidding_id]}</span>";
        }
        return $ret;
    }

    /**
     * 申告利害に、現在のユーザの共著関係をまとめたもの
     */
    public static function arr_pu_rigai()
    {
        $ret = [];
        foreach(RevConflict::all() as $a){
            $ret[$a->paper_id][$a->user_id] = $a->bidding_id; // 1が利害by著者,2が利害by査読者
        }
        // ユーザ自身の共著論文をとりよせる
        $my_uid = auth()->id();
        $me = User::find($my_uid);
        foreach($me->coauthor_papers() as $paper){
            if (isset($ret[$paper->paper_id][$my_uid])){
                $ret[$paper->paper_id][$my_uid] = 1;
            }
        }
        return $ret;
    }
}
