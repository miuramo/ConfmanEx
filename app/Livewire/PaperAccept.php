<?php

namespace App\Livewire;

use App\Models\Paper;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

use function Laravel\Prompts\info;

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
            self::$cats = Cache::rememberForever('cats', function () {
                return \App\Models\Category::pluck('name', 'id')->toArray();
            });
        }
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
        // Load paper and submits logic here
        $this->paper = Paper::find($this->paper_id);
        foreach ($this->paper->submits as $submit) {
            $this->submits[$submit->category_id] = $submit;
        }
    }
    public function render()
    {
        return view('livewire.paper-accept');
    }
    public function editAccept($category_id)
    {
        if (isset($this->submits[$category_id])) {
            $this->edit_category_id = $category_id;
        }
        $this->mount();
        $this->render();
    }
    // public function updatedSubmits($value, string $key)
    // {
    //     info("updatedSubmits: key=$key, value=$value");
    // }
    public function updated($property, $value): void
    {
        Log::debug('[updated] ' . $property, ['value' => $value]);
    }
}
