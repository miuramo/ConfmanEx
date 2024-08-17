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
    ];

    public function items()
    {
        return $this->hasMany(VoteItem::class, 'vote_id');
    }


    public static function init($isclose = 0)
    {
        Vote::firstOrCreate(
            [
                'name' => '優れた口頭発表に対する投票',
            ],
            [
                'isclose' => $isclose,
            ]
        );
        Vote::firstOrCreate(
            [
                'name' => '優れたデモ・ポスター発表に対する投票',
            ],
            [
                'isclose' => $isclose,
            ]
        );
    }
}
