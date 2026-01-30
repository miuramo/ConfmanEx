<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Regist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'valid',
        'paid',
        'paid_at',
        'payment_method',
        'payment_id',
        'payment_status',
        'confirmed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toArray()
    {
        $ary = [];
        $ary['状況'] = $this->valid ? '有効' : '無効';
        $ary['参加登録ID'] = $this->id;
        if ($this->user) $ary['参加者氏名・所属'] = $this->user->name . " （" . $this->user->affil . "）";
        $ary['申込日時'] = $this->submitted_at;
        $ary['申込種別'] = $this->isearly ? '早期申込' : '通常申込';
        return $ary;
    }

    /**
     * 編集用トークンを返す
     */
    public function token()
    {
        return sha1($this->id . $this->user_id . $this->created_at);
    }

    /**
     * スポンサー参加登録用トークンを返す
     */
    public static function sponsortoken()
    {
        $reg_early_limit = Setting::getval('REG_EARLY_LIMIT');
        return substr(sha1('sponsor' . $reg_early_limit), 0, 16);
    }

    /**
     * この参加登録に関連するアンケート回答(EnqueteAnswer)を返す
     * アンケートIDごとに配列化して返す
     * @return array [enquete_id][enquete_item_id] = EnqueteAnswer
     */
    public function enqans()
    {
        // EnqueteAnswers を返す
        $enqs = Enquete::needForRegist();
        $ids = array_keys($enqs['until']);
        // 既存回答
        $eans = EnqueteAnswer::where('user_id', $this->user_id)->whereIn('enquete_id', $ids)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
        }
        return $enqans;
    }

    /**
     * この参加登録に関連するアンケート回答(EnqueteAnswer)の
     * enqitm.name(質問項目keyname) => value 配列を返す
     * $this->check()と $this->ng_feedback() で使用
     */
    public function enq_key_value()
    {
        $enqans = $this->enqans();
        $enqitm_id_name = EnqueteItem::pluck('name', 'id')->toArray();
        $res = [];
        foreach ($enqans as $enqid => $items) {
            foreach ($items as $itemid => $ans) {
                $res[$enqitm_id_name[$itemid]] = $ans->valuestr;
            }
        }
        return $res;
    }
    /**
     * この参加登録に関連するアンケート回答(EnqueteAnswer)の
     * enqitm.desc(質問項目) => value 配列を返す
     * confirmation.blade.php で使用
     */
    public function enq_enqitmdesc_value()
    {
        $itemid_desc = EnqueteItem::enq_enqitmid_desc();//すべての EnqueteItem の id => desc 配列を取得
        $enqans = $this->enqans();
        $res = [];
        foreach ($enqans as $enqid => $items) {
            foreach ($items as $itemid => $ans) {
                $res[$itemid_desc[$itemid]] = $ans->valuestr;
            }
        }
        return $res;
    }

    public function check()
    {
        $ary = $this->enq_key_value();
        $res = Enquete::validateEnquetes(User::find($this->user_id));

        if (count($res) > 0) {
            return $res;
        }
        // 追加のチェック from Validation
        $vals = Validation::where('event_id', $this->event_id)->orderBy('orderint')->get(); // 現在はイベントID=1（参加登録）のみ
        foreach ($vals as $val) {
            // script を評価
            if (!empty($val->script)) {
                eval($val->script);
            }
            // closure を評価 (scriptとclosureの違いはあまりない。)
            if (!empty($val->closure)) {
                eval($val->closure);
            }
        }
        // $res[] = $this->chk_kubun($ary);
        // $res[] = $this->chk_othergakkai($ary);
        // $res[] = $this->chk_student($ary);
        // foreach ($ary as $k => $v) {
        //     $res[] = $k . ": " . $v;
        // }
        // $res[] = $this->nogood(['ismember' => 2, 'presen_charge' => 1]); // 現地論文発表は会員でないといけない
        // $res[] = $this->nogood(['ismember' => 2, 'kubun' => 1]); // 非会員は参加費一般を選択できない
        // $res[] = $this->nogood(['isstudent' => 1, 'kubun' => 2]); // 一般は学生参加費を選択できない
        // $res[] = $this->nogood(['ismember' => 1, 'kubun' => 3]); // 会員は非会員参加費を選択できない
        return $res;
    }

    /**
     * NGになる組み合わせで回答されているかどうかを判定する
     * NGになるルールは、配列で ['is_student' => 2, 'kubun' => [3,4]] のように与える
     * ルールは3要素以上でもよい。 NGになるとは、すべての要素にマッチする場合を指す
     */
    public function nogood($ruleary)
    {
        $selids = EventConfig::getEnqueteAnswersBySelectionNumber($this->event_id, $this->user_id);
        $num_match = 0;
        foreach ($ruleary as $key => $rule) {
            if (is_array($rule)) {
                foreach ($rule as $selid) {
                    if (isset($selids[$key]) && $selids[$key] == $selid) {
                        $num_match++;
                        break;
                    }
                }
            } else {
                if (isset($selids[$key]) && $selids[$key] == $rule) {
                    $num_match++;
                }
            }
        }
        if ($num_match == count($ruleary)) {
            // フィードバック（なにがNGなのか、その理由）を生成して返す。
            return $this->ng_feedback($ruleary);
        }
    }
    public function ng_feedback($ruleary)
    {
        $ans = $this->enq_key_value();
        $descs = EnqueteItem::pluck('desc', 'name')->toArray();
        $selected = [];
        foreach ($ruleary as $key => $rule) {
            $selected[] = "【" . $descs[$key] . "】を『" . $ans[$key]."』";
        }
        if (count($ruleary) == 2) {
            $msg = $selected[0] . "にしたとき、" . $selected[1] . " にすることはできません。";
        } else {
            $msg = "以下の組み合わせは選択できません：";
            $msg .= implode("かつ、", $selected) . "のとき。";
        }
        return $msg;
    }

    public function chk_kubun($ary)
    {
        if (empty($ary['kubun'])) {
            return "参加区分を選択してください。";
        }
        if (empty($ary['gakkai'])) {
            return "学会を選択してください。";
        }
        if (strpos($ary['kubun'], "学会会員") !== false) {
            if (strpos($ary['gakkai'], "非会員") !== false) {
                return "参加区分→「学会会員」を選択した場合は、学会は「非会員以外」を選択してください。";
            }
            if (empty($ary['kaiinid'])) {
                return "参加区分→「学会会員」を選択した場合は、上記で入力した学会の会員番号を入力してください。";
            }
        } else if (strpos($ary['kubun'], "非会員") !== false) {
            if (strpos($ary['gakkai'], "非会員") === false) {
                return "参加区分→「非会員」を選択した場合は、学会は「非会員」を選択してください。";
            }
        }
    }
    public function chk_othergakkai($ary)
    {
        if (empty($ary['gakkai'])) {
            return "学会を選択してください。";
        }
        if (strpos($ary['gakkai'], "その他") !== false) {
            if (empty($ary['othergakkai'])) {
                return "学会→「その他」を選択した場合は、「その他の学会名」を入力してください。";
            }
            if (empty($ary['kaiinid'])) {
                return "学会→「その他」を選択した場合は、「上記で入力した学会の会員番号」を入力してください。";
            }
        } else if (strpos($ary['gakkai'], "非会員") !== false) {
            if ($ary['kaiinid'] !== "非会員") {
                return "学会→「非会員」を選択した場合は、「上記で入力した学会の会員番号」には漢字3文字で『非会員』と入力してください。";
            }
        } else {
            if (!empty($ary['othergakkai'])) {
                return "学会→「その他」を選択していない場合は、「その他の学会名」を入力しないでください。";
            }
        }
    }
    public function chk_student($ary)
    {
        if (empty($ary['isstudent'])) {
            return "種別（一般 / 学生）を選択してください。";
        }
        if (empty($ary['kubun'])) {
            return "参加区分を選択してください。";
        }
        if ($ary['isstudent'] == "一般") { // 一般で回答していて
            if (preg_match("/発表のある学生/", $ary['kubun']) || preg_match("/学生ボランティア/", $ary['kubun'])) {
                return "参加区分で「学生・・・」を選択した場合は、種別（一般 / 学生）でも学生を選択してください。";
            }
        }
    }

    public static function countByItemAndIsearly($enqitm_name = 'kubun')
    {
        // $key の回答enquete_item_id を取得
        $enquete_item_target = EnqueteItem::where('name', $enqitm_name)->first();
        if (!$enquete_item_target) {
            return [];
        }
        $res = Regist::where('valid', 1)
            ->leftJoin('enquete_answers', function ($join) use ($enquete_item_target) {
                $join->on('regists.user_id', '=', 'enquete_answers.user_id')
                    ->where('enquete_answers.enquete_item_id', $enquete_item_target->id);
                $join->where('enquete_answers.paper_id', '>', 0); // ここで、アンケートプレビューからの重複回答を排除
            })
            ->selectRaw('enquete_answers.valuestr as ' . $enqitm_name . ', regists.isearly, count(*) as cnt')
            ->groupBy($enqitm_name, 'isearly')
            ->orderBy('isearly', 'desc')
            ->get();
        $ret = [];
        foreach ($res as $r) {
            $ret[$r->$enqitm_name][$r->isearly] = $r->cnt;
        }
        return $ret;
    }
}
