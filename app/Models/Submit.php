<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submit extends Model
{
    use HasFactory;

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

    public static function subs_accepted(int $cat_id, string $ord = "orderint")
    {
        $subs = Submit::with('paper')->where("category_id", $cat_id)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->orderBy($ord)->get();
        return $subs;
    }
}
