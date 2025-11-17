<?php

namespace App\Livewire;

use App\Models\Paper;
use Livewire\Component;

class PaperAccept extends Component
{
    public $paper_id = null;
    public $paper_title = '';
    public $paper = null;
    public $submits = []; // key=category_id, value=submit
    static public $cats = null;
    static public $accepts = null;
    static public $judges = null;
    static public $catcolors = null;

    public $edit_category_id = 0;

    public function mount()
    {
        if (self::$cats === null) {
            self::$cats = \App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        }
        if (self::$accepts === null) {
            self::$accepts = \App\Models\Accept::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        }
        if (self::$judges === null) {
            self::$judges = \App\Models\Accept::select('id', 'judge')->get()->pluck('judge', 'id')->toArray();
        }
        if (self::$catcolors === null) {
            self::$catcolors = \App\Models\Category::select('id', 'bgcolor')->get()->pluck('bgcolor', 'id')->toArray();
        }
        // Load paper and submits logic here
        $this->paper = Paper::find($this->paper_id);
        foreach($this->paper->submits as $submit) {
            $this->submits[$submit->category_id] = $submit;
        }
    }
    public function render()
    {
        return view('livewire.paper-accept');
    }
    public function editAccept($category_id)
    {
        $this->mount();
        $this->render();
        // if (isset($this->submits[$category_id])) {
        //     $this->edit_category_id = $category_id;
        // }
        // $this->render();
    }
    public function updated()
    {
        $this->mount();
        $this->render();
    }
}
