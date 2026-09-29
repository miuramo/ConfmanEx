<?php

namespace App\Livewire;

use App\Models\Regist;
use Livewire\Component;

class RegistMockCheck extends Component
{
    public array $errors = ['まだチェックされていません。入力内容チェックを行ってください。'];
    public bool $checked = false;

    public function checkMockAnswers(array $answers): void
    {
        $regist = new Regist();
        $regist->event_id = 1;
        $this->errors = array_values(array_filter($regist->check($answers), function ($value) {
            return !is_null($value);
        }));
        $this->checked = true;
    }

    public function render()
    {
        return view('livewire.regist-mock-check');
    }
}