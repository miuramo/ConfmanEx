<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnqueteItem extends Model
{
    use HasFactory;

    public function enquete()
    {
        return $this->belongsTo(Enquete::class);
    }

    /**
     * バリデーション
     */
    public function validate_rule(string $val)
    {
        if ($val == null) return true;
        if ($this->pregrule == null) return true;
        if (is_numeric($val)) $val = strval($val);
        $val = mb_convert_kana($val, 'a', 'UTF-8');
        return preg_match($this->pregrule, $val);
    }

    public function selections()
    {
        $ary = explode(Viewpoint::$separator, $this->content); //改行ではなく、セミコロン ; で区切っていることに注意
        $item_title = nl2br(trim($ary[0])); // 最初の要素は、説明
        $type = trim($ary[1]); // 次は、formの種類

        if ($type == 'selection') {
            $sel = array_map('trim', array_slice($ary, 2)); // 選択肢やオプション
            return $sel;
        }
        return [];
    }

    public static function getDescAndSelText(string $key, int $selid){
        $item = EnqueteItem::where("name", $key)->first(); // name はユニークであることを想定
        if(!$item) return null;
        $selections = $item->selections();
        if(!isset($selections[$selid-1])) return null;
        return ["desc"=> $item->desc, "text"=> $selections[$selid-1]];
    }
}
