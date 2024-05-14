<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnqueteAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquete_id',
        'enquete_item_id',
        'user_id',
        'paper_id',
        'value',
        'valuestr',
    ];

    public function item()
    {
        return $this->belongsTo(EnqueteItem::class, 'enquete_item_id');
    }

    // アンケート showonpaperindex
    // [paperid][enqid][name1] = value1
    // [paperid][enqid][name2] = value2
    public static function getAnswers()
    {
        $showonEnq = Enquete::where("showonpaperindex", true)->get()->pluck('id')->toArray();

        $all = EnqueteAnswer::with('item')->whereIn('enquete_id', $showonEnq)
            ->orderBy("paper_id")->orderBy("enquete_id")->orderBy("enquete_item_id")->get();
        $ret = [];
        foreach ($all as $ea) {
            // if (isset($ea->item))
            $ret[$ea->paper_id][$ea->enquete_id][$ea->item->name] = $ea->valuestr;
        }
        return $ret;
    }
}
