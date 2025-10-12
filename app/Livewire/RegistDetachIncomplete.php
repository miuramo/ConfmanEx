<?php

namespace App\Livewire;

use Livewire\Component;

class RegistDetachIncomplete extends Component
{
    public $notfinished = [];

    public function mount()
    {
        $this->notfinished = \App\Models\Regist::with('user')->where('valid', 0)->orderby('created_at')->get();
    }
    public function render()
    {
        return view('livewire.regist-detach-incomplete');
    }
}
