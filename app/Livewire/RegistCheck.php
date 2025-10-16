<?php

namespace App\Livewire;

use DateTime;
use Livewire\Component;

class RegistCheck extends Component
{
    public $regid;
    public $regobj;
    public $errors = ['まだチェックされていません。入力内容チェックを行ってください。'];
    public $reg_early_limit;
    public $is_early = false;
    public function render()
    {
        $this->check();
        return view('livewire.regist-check');
    }

    // 参加登録のチェックを行う
    public function check()
    {
        $this->errors = []; // エラーをリセット
        // $this->reg_early_limit = \App\Models\Setting::getval("REG_EARLY_LIMIT");
        $this->is_early = auth()->user()->can('is_now_early');
        $this->regobj = \App\Models\Regist::find($this->regid);
        if ($this->regobj == null) {
            $this->errors[] = "★★★参加登録データが見つかりません。参加登録をやり直してください。★★★";
            return;
        }
        $this->errors = $this->regobj->check();
        $this->errors = array_filter($this->errors, function ($value) {
            return !is_null($value);
        });
        if ($this->regobj->submitted_at != null) {
            if (count($this->errors) > 0) {
                $this->errors[] = "★★★この参加登録は以前に完了していますが、入力内容が修正された際に問題がみつかったため、登録が無効になっています。★★★";
                $this->regobj->valid = false;
                $this->regobj->save();
            }
        }
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
        } else {
            // if ($regobj->user_id == auth()->id()) {
            //     // 自分の登録を編集した場合のみ、更新日時を更新する。会計担当の場合は更新しない。
            $regobj->updated_at = now();
            // }
            $regobj->valid = true; // 参加登録を有効にする
            $regobj->save();
        }
        // 参加登録を行う処理を実装する
        // ここでは仮に成功メッセージを表示するだけ
        // session()->flash('feedback.success', '参加登録が完了しました。');
        if ($regobj->user_id != auth()->id()) {
            return redirect()->route('role.top', ['role' => 'acc'])->with('feedback.success', $regobj->user->name . 'さんの登録を、代理で編集しました。');
        }
        return redirect()->route('regist.index')->with('feedback.success', '参加登録が完了しました。');
    }
}
