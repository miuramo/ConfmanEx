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

    /**
     * 指定された key と選択肢IDから、説明文と選択肢テキストを取得する
     * たとえば、key = ismenber、selid=2 を指定すると、
     * ["desc"=> "会員種別", "text"=> "非会員"] のような配列を返す
     * 見つからない場合は null を返す
     * 注意：keyはユニークであることを想定。複数あった場合は最初の1件のみ返す
     */
    public static function getDescAndSelText(string $key, int $selid)
    {
        $item = EnqueteItem::where("name", $key)->first(); // name はユニークであることを想定
        if (!$item) return null;
        $selections = $item->selections();
        if (!isset($selections[$selid - 1])) return null;
        return ["desc" => $item->desc, "text" => $selections[$selid - 1]];
    }

    /**
     * 全 EnqueteItem の id => desc 配列を返す
     */
    public static function enq_enqitmid_desc()
    {
        // TODO: 他のアンケートと、name の重複がないようにする必要あり。name=>idにするのも一つの方法。
        $enqitm_id_desc = EnqueteItem::pluck('desc', 'id')->toArray();
        return $enqitm_id_desc;
    }
}
