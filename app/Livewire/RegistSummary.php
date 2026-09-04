<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class RegistSummary extends Component
{
    public $finishedCount = 0;
    public $notfinishedCount = 0;
    public $summary = [];
    public $items = ["kubun", "volunteer", "zenpaku", "roomshare", "bus1", "bus2"];

    public function mount()
    {
        $this->finishedCount = \App\Models\Regist::where('valid', 1)->count();
        $this->notfinishedCount = \App\Models\Regist::where('valid', 0)->count();

        // もしSetting::get
        $reg_sum_items = Setting::getval("REGIST_SUMMARY_ITEMS");
        $reg_sum_items = json_decode($reg_sum_items);
        if (is_array($reg_sum_items)) {
            $this->items = $reg_sum_items;
        }
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
