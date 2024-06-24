<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewpoint extends Model
{
    use HasFactory;

    public static $separator = ';' ; // semi-colon is better than colon

    protected $fillable = [
        'category_id',
        'orderint',
        'name',
        'desc',
        'content',
        'contentafter',
        'forrev',
        'formeta',
        'weight',
        'doReturn',
        'doReturnAcceptOnly',

    ];

    /**
     * コロンではなくセミコロンに変更
     */
    public static function change_separator()
    {
        $pre = ":";
        $post = self::$separator;
        // すべての査読観点を取得
        $vps = Viewpoint::all();

        $log = "Viewpoint\n";
        // 各レコードのcontentフィールドの「:」を「;」に置換
        foreach ($vps as $vp) {
            $vp->content = str_replace($pre, $post, $vp->content);
            // $vp->contentafter = str_replace($pre, $post, $vp->contentafter);
            $vp->save();
            $log .= $vp->id." ";
        }
        // アンケート項目も同様に。
        $log .= "\nEnqueteItem\n";
        $enqitems = EnqueteItem::all();
        foreach($enqitems as $vp) {
            $vp->content = str_replace($pre, $post, $vp->content);
            // $vp->contentafter = str_replace($pre, $post, $vp->contentafter);
            $vp->save();
            $log .= $vp->id." ";
        }
        return $log;
    }
}
