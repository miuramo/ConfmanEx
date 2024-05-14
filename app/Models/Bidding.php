<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidding extends Model
{
    use HasFactory;

    /**
     * used by ReviewController@conflict
     */
    public static function revcondiv()
    {
        $bids = Bidding::all();
        $revcondiv = [];
        foreach($bids as $b){
            $revcondiv[$b->id] = '<div class="bg-'.$b->bgcolor.'-200 text-2xl mx-4 mb-1 p-4 rounded-md dark:bg-'.$b->bgcolor.'-700">'.$b->name.'</div>';
        }
        return $revcondiv;
    }
}
