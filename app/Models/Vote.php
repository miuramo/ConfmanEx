<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'isclose',
        'for_pc',
    ];

    public function items()
    {
        return $this->hasMany(VoteItem::class, 'vote_id');
    }


    public static function init($isclose = 0)
    {
        Vote::firstOrCreate(
            [
                'name' => '口頭発表',
            ],
            [
                'isclose' => $isclose,
            ]
        );
        Vote::firstOrCreate(
            [
                'name' => 'デモ・ポスター発表',
            ],
            [
                'isclose' => $isclose,
            ]
        );
        Vote::firstOrCreate(
            [
                'name' => '論文賞',
            ],
            [
                'isclose' => $isclose,
                'for_pc' => 1, // 論文賞はPC投票用フラグを立てる
            ]
        );
    }
}
