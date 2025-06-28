<?php

namespace App\Livewire;

use DateTime;
use Livewire\Component;

class RegistCheck extends Component
{
    public $regid;
    public $errors = ['まだチェックされていません。入力チェックを行ってください。'];
    public function render()
    {
        return view('livewire.regist-check');
    }

// 参加登録のチェックを行う
    public function check()
    {
        $this->errors = []; // エラーをリセット
        $regobj = \App\Models\Regist::find($this->regid);
        $this->errors = $regobj->check();
        $this->errors = array_filter($this->errors, function ($value) {
            return !is_null($value);
        });
    }

    public function doregist()
    {
        $regobj = \App\Models\Regist::find($this->regid);
        $regobj->submitted_at = now();
        $regobj->valid = true; // 参加登録を有効にする
        $regobj->isearly = new DateTime() < new DateTime("2025-06-28 22:47:00"); // 早期登録かどうかを判定
        $regobj->save();
        // 参加登録を行う処理を実装する
        // ここでは仮に成功メッセージを表示するだけ
        // session()->flash('feedback.success', '参加登録が完了しました。');
        return redirect()->route('regist.index')->with('feedback.success', '参加登録が完了しました。');
    }
}
