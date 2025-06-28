<?php

namespace App\Livewire;

use Livewire\Component;

class RegistCheck extends Component
{
    public $regid;
    public $errors = [];
    public function render()
    {
        return view('livewire.regist-check');
    }

    public function check()
    {
        $this->errors = []; // エラーをリセット
        $regobj = \App\Models\Regist::find($this->regid);
        $this->errors = $regobj->check();
        $this->errors = array_filter($this->errors, function ($value) {
            return !is_null($value);
        });

        // // 参加登録のチェックを行う
        // // チェック結果を表示する処理を実装する
        // // ここでは仮にチェック結果を表示するだけ
        // session()->flash('message', 'チェックが完了しました。');
    }

    public function doregist()
    {
        // 参加登録を行う処理を実装する
        // ここでは仮に成功メッセージを表示するだけ
        session()->flash('message', '参加登録が完了しました。');
    }
}
