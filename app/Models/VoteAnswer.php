<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VoteAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vote_id',
        'submit_id',
        'booth',
        'valid',
        'comment',
        'token',
    ];

    public function item()
    {
        return $this->belongsTo(VoteItem::class, 'vote_item_id');
    }

    public static function vote_result()
    {
        $ret = [];
        $tmp = VoteAnswer::select(DB::raw("count(id) as count, booth, vote_id, valid"))
            ->where("valid", ">", 0)
            ->groupBy("vote_id")
            ->groupBy("valid")
            ->groupBy("booth")
            ->orderBy("vote_id")
            ->orderBy("valid")
            ->orderByDesc("count")
            ->orderBy("booth")
            ->get();
        foreach ($tmp as $t) {
            $ret[$t->vote_id][$t->valid][$t->booth] = $t->count;
        }
        return $ret;
    }
}
