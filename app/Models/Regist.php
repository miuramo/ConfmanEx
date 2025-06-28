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

    public function check()
    {
        $ary = $this->enq_key_value();
        $res = Enquete::validateEnquetes(User::find($this->user_id));
        $res[] = $this->chk_kubun($ary);
        $res[] = $this->chk_othergakkai($ary);
        return $res;
    }

    public function chk_kubun($ary)
    {
        if (empty($ary['kubun'])) {
            return "参加区分を選択してください。";
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
            if (!empty($ary['kaiinid'])) {
                return "学会→「非会員」を選択した場合は、「上記で入力した学会の会員番号」は（未入力）のままにしてください。";
            }
        } else {
            if (!empty($ary['othergakkai'])) {
                return "学会→「その他」を選択していない場合は、「その他の学会名」を入力しないでください。";
            }
        }
    }
}
