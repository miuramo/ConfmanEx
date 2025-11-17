<?php

namespace App\Livewire;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SubmitBooth extends Component
{
    static public $accepts = null;
    static public $catcolors = null;
    static public $judges = null;

    public $submit_id;
    public $booth;
    public $cid;

    public $isEditing = false;
    public $old_value = null;

    public function mount()
    {
        if (self::$accepts === null) {
            self::$accepts = Cache::rememberForever('accepts', function () {
                return \App\Models\Accept::pluck('name', 'id')->toArray();
            });
        }
        if (self::$judges === null) {
            self::$judges = Cache::rememberForever('judges', function () {
                return \App\Models\Accept::pluck('judge', 'id')->toArray();
            });
        }
        if (self::$catcolors === null) {
            self::$catcolors = Cache::rememberForever('catcolors', function () {
                return \App\Models\Category::pluck('bgcolor', 'id')->toArray();
            });
        }
    }
    public function render()
    {
        $this->mount();
        return view('livewire.submit-booth');
    }

    public function editBooth()
    {
        $this->isEditing = true;
        $this->old_value = $this->booth;
    }
    public function updatedBooth()
    {
        $sub = \App\Models\Submit::find($this->submit_id);
        $sub->booth = $this->booth;
        $sub->save();
        $this->isEditing = false;
        $this->render();
    }
    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->booth = $this->old_value;
    }
    public function save(){
        $this->updatedBooth();
    }
}
