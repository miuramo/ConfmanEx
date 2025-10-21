<?php

namespace App\Livewire;

use Livewire\Component;

class RegistSummary extends Component
{
    public $finishedCount = 0;
    public $notfinishedCount = 0;
    public $summary = [];
    public $items = ["kubun", "volunteer", "zenpaku", "roomshare", "bus1","bus2"];

    public function mount()
    {
        $this->finishedCount = \App\Models\Regist::where('valid', 1)->count();
        $this->notfinishedCount = \App\Models\Regist::where('valid', 0)->count();

        $summary = [];
        foreach ($this->items as $itm) {
            $summary[$itm] = \App\Models\Regist::countByItemAndIsearly($itm);
        }
        $this->summary = $summary;
    }

    public function render()
    {
        return view('livewire.regist-summary');
    }
}
