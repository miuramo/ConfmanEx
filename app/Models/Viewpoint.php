<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewpoint extends Model
{
    use HasFactory;

    public static $separator = ';'; // semi-colon is better than colon

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
        // もし ;(post) が含まれていない and  :(pre) が含まれている場合のみ変更
        // 各レコードのcontentフィールドの「:」を「;」に置換
        foreach ($vps as $vp) {
            if (strpos($vp->content, $post) === false) {
                if (strpos($vp->content, $pre) !== false) {
                    $vp->content = str_replace($pre, $post, $vp->content);
                    $vp->save();
                    $log .= $vp->id . " ";
                }
            }
        }
        // アンケート項目も同様に。
        $log .= "\nEnqueteItem\n";
        $enqitems = EnqueteItem::all();
        foreach ($enqitems as $vp) {
            // もし ;(post) が含まれていない and  :(pre) が含まれている場合のみ変更
            if (strpos($vp->content, $post) === false) {
                if (strpos($vp->content, $pre) !== false) {
                    $vp->content = str_replace($pre, $post, $vp->content);
                    $vp->save();
                    $log .= $vp->id . " ";
                }
            }
        }
        return $log;
    }

    /**
     * OrderInt をstep ずつで再設定する
     */
    public static function reorderint($cat_id, $step = 10)
    {
        $items = Viewpoint::where("category_id", $cat_id)->orderBy("orderint")->get();
        $num = $step;
        foreach ($items as $enqitm) {
            $enqitm->orderint = $num;
            $enqitm->save();
            $num += $step;
        }
    }
}
