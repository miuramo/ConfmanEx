<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SubmitAccept extends Component
{
    static public $accepts = null;
    static public $catcolors = null;
    static public $judges = null;

    public $submit_id;
    public $accept_id;
    public $cid;
    public $paper_id = null;

    public $isEditing = false;
    public $canDelete = false;

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
        return view('livewire.submit-accept');
    }
    public function editAccept()
    {
        $this->isEditing = true;
        if ($this->submit_id == 0){
            $this->canDelete = true;
        } else {
            $sub = \App\Models\Submit::find($this->submit_id);
            $this->canDelete = ($sub->score == null);
        }
    }
    public function updatedAcceptId()
    {
        if ($this->submit_id == 0){
            $sub = \App\Models\Submit::firstOrCreate([
                'paper_id' => $this->paper_id,
                'category_id' => $this->cid,
            ],[
                'accept_id' => 1,
            ]);
            $this->submit_id = $sub->id;
            $sub->save();
        };
        $sub = \App\Models\Submit::find($this->submit_id);
        $sub->accept_id = $this->accept_id;
        $sub->save();
        $this->isEditing = false;
        $this->render();
    }
    public function cancelEdit()
    {
        $this->isEditing = false;
    }
    public function deleteSubmit()
    {
        if ($this->submit_id == 0){
            return;
        }
        $sub = \App\Models\Submit::find($this->submit_id);
        if ($sub->score != null){
            return;
        }
        $sub->delete();
        $this->submit_id = 0;
        $this->accept_id = 0;
        $this->isEditing = false;
        $this->render();
    }
}
