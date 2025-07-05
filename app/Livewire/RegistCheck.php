<?php

namespace App\Livewire;

use DateTime;
use Livewire\Component;

class RegistCheck extends Component
{
    public $regid;
    public $errors = ['まだチェックされていません。入力内容チェックを行ってください。'];
    public $reg_early_limit;
    public $is_early = false;
    public function render()
    {
        return view('livewire.regist-check');
    }

    // 参加登録のチェックを行う
    public function check()
    {
        $this->errors = []; // エラーをリセット
        $this->reg_early_limit = \App\Models\Setting::getval("REG_EARLY_LIMIT");
        $this->is_early = new DateTime() <= new DateTime($this->reg_early_limit." 23:59:59");
        $regobj = \App\Models\Regist::find($this->regid);
        $this->errors = $regobj->check();
        $this->errors = array_filter($this->errors, function ($value) {
            return !is_null($value);
        });
    }

    public function doregist()
    {
        $regobj = \App\Models\Regist::find($this->regid);

        // まだ参加登録が行われていない場合、登録日時を設定し、有効にする
        if ($regobj->submitted_at == null) {
            $regobj->submitted_at = now();
            $regobj->valid = true; // 参加登録を有効にする
            $regobj->isearly = $this->is_early; // 早期登録かどうかを判定
            $regobj->save();
        }
        // 参加登録を行う処理を実装する
        // ここでは仮に成功メッセージを表示するだけ
        // session()->flash('feedback.success', '参加登録が完了しました。');
        return redirect()->route('regist.index')->with('feedback.success', '参加登録が完了しました。');
    }
}
