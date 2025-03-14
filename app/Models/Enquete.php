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

    public function roles()
    {
        $tbl = 'enquete_roles';
        return $this->belongsToMany(Role::class, $tbl);
    }

    public function getkey(int $len = 5)
    {
        return substr(sha1($this->id . $this->name), 0, $len);
    }
    /**
     * デモ希望をだしているPaperID を返す
     */
    public static function paperids_demoifaccepted($cat_id)
    {
        $demoenqitem = EnqueteItem::where("name", "demoifaccepted")->first();
        if ($demoenqitem != null) {
            $demoenqitemid = $demoenqitem->id;
            $papers = Paper::where('category_id', $cat_id)->get();
            $res = [];
            foreach ($papers as $paper) {
                $ans = $paper->enqans->where("enquete_item_id", $demoenqitemid)->first();
                if ($ans != null && $ans->valuestr == "はい") {
                    $res[] = $paper->id;
                }
            }
            return $res;
        }
        return [];
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
        $configs = EnqueteConfig::where('valid', 1)->orderBy('openstart', 'desc')->get();
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

    /**
     * 期間内かどうか
     * 06-01 〜 10-31 のように、begin<endなら、そのまま
     * 11-01 〜 03-31 のように、begin>endの場合、
     * 単純に、ひっくり返して、条件を反転すればよい
     */
    public static function checkdayduration($openstart, $openend)
    {
        $s = array_map("intval", explode("-", $openstart));
        $e = array_map("intval", explode("-", $openend));
        $month = date('n');
        $day = date('j');

        $now = $month * 100 + $day; // 06-01 なら 0601
        $begin = $s[0] * 100 + $s[1];
        $end = $e[0] * 100 + $e[1];
        if ($begin < $end) {
            return ($begin <= $now && $now <= $end);
        } else {
            $tmp = $begin;
            $begin = $end;
            $end = $tmp;
            return !($begin < $now && $now < $end);
        }
    }

    public static function mm_dd_fancy($mmdd)
    {
        $e = array_map("intval", explode("-", $mmdd));
        return "{$e[0]}月{$e[1]}日";
    }

    /**
     * OrderInt をstep ずつで再設定する
     */
    public static function reorderint($step = 10)
    {
        $all = Enquete::all();
        foreach ($all as $enq) {
            $num = $step;
            foreach ($enq->items as $enqitm) {
                $enqitm->orderint = $num;
                $enqitm->save();
                $num += $step;
            }
        }
    }
    /**
     * ユーザが参照・編集可能なアンケートを返す
     * 
     * 配列で返すなら、$returnAry = true
     */
    public static function accessibleEnquetes($returnAry = false)
    {
        $uid = auth()->id();
        $rolename_id = User::find($uid)
            ->roles->pluck("id", "name")
            ->toArray();
        // PCをもっていれば、ぜんぶみれる
        if (isset($rolename_id['pc'])) {
            if ($returnAry) return Enquete::select("id", "name")->get()->pluck("name", "id")->toArray();
            return Enquete::with("roles")->get();
        }
        if ($returnAry) {
            return Enquete::select("id", "name")->whereHas('roles', function ($query) use ($rolename_id) {
                $query->whereIn('roles.id', array_values($rolename_id));
            })->get()->pluck("name", "id")->toArray();
        }
        // それ以外は、自分が所属しているroleから、参照許可されているアンケートをかえす。
        return Enquete::with("roles")->whereHas('roles', function ($query) use ($rolename_id) {
            $query->whereIn('roles.id', array_values($rolename_id));
        })->get();
    }
}
