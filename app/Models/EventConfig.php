<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventConfig extends Model
{
    use HasFactory;

    // イベントID に対応するアンケートIDから、関連するEnqueteItemsを取得
    public static function getEnqueteItems(int $event_id)
    {
        $enqids = EventConfig::where('event_id', $event_id)->pluck('enquete_id')->toArray();
        if (count($enqids) == 0) {
            return [];
        }
        $items = EnqueteItem::whereIn('enquete_id', $enqids)->get();
        return $items;
    }

    public static function getEnqueteAnswers(int $event_id, int $user_id)
    {
        $enqids = EventConfig::where('event_id', $event_id)->pluck('enquete_id')->toArray();
        if (count($enqids) == 0) {
            return [];
        }
        $answers = EnqueteAnswer::whereIn('enquete_id', $enqids)->where('user_id', $user_id)->get();
        return $answers;
    }

    // 参加申込時の選択項目について、テキストではなく選択肢番号(1,2,3)で返す。
    public static function getEnqueteAnswersBySelectionNumber(int $event_id, int $user_id)
    {
        $answers = EventConfig::getEnqueteAnswers($event_id, $user_id);
        $enqids = EventConfig::where('event_id', $event_id)->pluck('enquete_id')->toArray();
        if (count($enqids) == 0) {
            return [];
        }
        $answers = EnqueteAnswer::whereIn('enquete_id', $enqids)->get();
        $key_selids = [];
        foreach ($answers as $ans) {
            $item = EnqueteItem::find($ans->enquete_item_id);
            $sel_options = $item->selections();
            $value_id = array_flip($sel_options);
            if (isset($value_id[$ans->valuestr])) {
                $key_selids[$item->name] = $value_id[$ans->valuestr] + 1; // 選択肢番号は1始まりとする
            }
        }
        return $key_selids;
    }
}
