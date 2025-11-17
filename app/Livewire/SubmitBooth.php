<?php

namespace App\Livewire;

use Livewire\Component;

class SubmitBooth extends Component
{
    public $accepts;
    public $catcolors;
    public $judges;

    public $submit_id;
    public $booth;
    public $cid;

    public $isEditing = false;

    public function render()
    {
        return view('livewire.submit-booth');
    }

    public function editBooth()
    {
        $this->isEditing = true;
    }
    public function updatedBooth()
    {
        $sub = \App\Models\Submit::find($this->submit_id);
        $sub->booth = $this->booth;
        $sub->save();
        $this->isEditing = false;
        $this->render();
    }
}
