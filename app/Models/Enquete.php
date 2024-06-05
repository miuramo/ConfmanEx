<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquete extends Model
{
    use HasFactory;

    public function items()
    {
        // 並び順を orderint にする
        return $this->hasMany(EnqueteItem::class, 'enquete_id')->orderBy('orderint');
    }
    /**
     * 必要なアンケートを返す
     */
    public static function needForSubmit(Paper $paper)
    {
        $cat_id = $paper->category_id;

        // 登壇デモ希望のEnqItemID
        $demoenqitem = EnqueteItem::where("name", "demoifaccepted")->first();
        if ($demoenqitem != null) {
            $demoenqitemid = $demoenqitem->id;
            $willDemo = (($paper->enqans->where("enquete_item_id", $demoenqitemid)->first()->valuestr ?? null) === "はい"); // demoifaccepted
        } else {
            $willDemo = false;
        }
        $matcher = ($willDemo) ? "d" . $cat_id : $cat_id;
        $configs = EnqueteConfig::all();
        $canedit = [];
        $readonly = [];
        $until = []; //enqid=>deadline
        foreach ($configs as $config) {
            $pass = false;
            if (Enquete::in_csv($config->catcsv, $matcher)) $pass = true;
            if (!$pass && Enquete::in_csv($config->catcsv, $cat_id)) $pass = true;
            if (!$pass) continue;
            $enq = Enquete::with('items')->find($config->enquete_id);
            if (Enquete::checkdayduration($config->openstart, $config->openend)) {
                $canedit[] = $enq;
            } else {
                $readonly[] = $enq;
            }
            $until[$enq->id] = Enquete::mm_dd_fancy($config->openend);
        }
        return ["canedit" => $canedit, "readonly" => $readonly, "until" => $until];
    }

    /**
     * 参加登録に必要なアンケートを返す
     */
    public static function needForPart(Participant $part)
    {
        $configs = EventConfig::where('event_id', $part->event_id)->orderBy('orderint')->get();
        $canedit = [];
        $readonly = [];
        $until = []; //enqid=>deadline
        // $ids = []; // あつめたEnqueteID
        foreach ($configs as $config) {
            $enq = Enquete::with('items')->find($config->enquete_id);
            if (Enquete::checkdayduration($config->openstart, $config->openend)) {
                $canedit[] = $enq;
            } else {
                $readonly[] = $enq;
            }
            $until[$enq->id] = Enquete::mm_dd_fancy($config->openend);
        }
        return ["canedit" => $canedit, "readonly" => $readonly, "until" => $until];
    }

    public static function validateEnquetes(Paper $paper)
    {
        $errorary = [];
        $needFor = Enquete::needForSubmit($paper)['canedit'];
        foreach ($needFor as $enq) {
            $res = $enq->validateOneEnq($paper);
            if (count($res) > 0) {
                foreach ($res as $n => $desc) {
                    $errorary[] = "【{$enq->name}→{$desc}】に回答してください。";
                }
            }
        }
        return $errorary;
    }

    /**
     * 未回答アンケート項目(EnqItem) id=>desc の配列をかえす。[] ならエラーなし。
     */
    public function validateOneEnq(Paper $paper)
    {
        $eis = $this->items;
        // exist answers: select enquete_item_id from enquete_answers where paper_id =
        $eas = EnqueteAnswer::where('paper_id', $paper->id)
            ->whereNotNull('valuestr')
            ->get()->pluck('enquete_item_id')->toArray();
        // return $eas;

        // 必須(is_mandatory=true)のitemだけを対象にし、easがないものを返す。
        $eis = $this->items->where('is_mandatory', true)->whereNotIn('id', $eas)->pluck('desc', 'id')->toArray();
        return $eis;
    }

    public static function in_csv($csv, $findlet)
    {
        $arycsv = explode(",", $csv);
        foreach ($arycsv as $n => $v) {
            if ($v == $findlet) return true;
        }
        return false;
    }

    public static function checkdayduration($openstart, $openend)
    {
        $s = array_map("intval", explode("-", $openstart));
        $e = array_map("intval", explode("-", $openend));
        $nextyear = ($e[0] < $s[0]);  // 終了月のほうが開始月より小さいなら、終了は次の年
        $year = date('Y');
        $month = date('n');
        $day = date('j');
        $now = mktime(12, 12, 12, $month, $day); // 12:12:12に深い意味なし
        $begin = mktime(12, 12, 12, intval($s[0]), intval($s[1]));
        $end = mktime(12, 12, 12, intval($e[0]), intval($e[1]), $year + intval($nextyear));
        return ($begin <= $now && $now <= $end);
    }

    public static function mm_dd_fancy($mmdd)
    {
        $e = array_map("intval", explode("-", $mmdd));
        return "{$e[0]}月{$e[1]}日";
    }
}
