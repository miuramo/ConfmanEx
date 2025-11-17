<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SubmitAccept extends Component
{
    public $accepts;
    public $catcolors;
    public $judges;

    public $submit_id;
    public $accept_id;
    public $cid;

    public $isEditing = false;

    public function render()
    {
        return view('livewire.submit-accept');
    }
    public function editAccept()
    {
        $this->isEditing = true;
    }
    public function updatedAcceptId()
    {
        Log::debug("Saving accept_id {$this->accept_id} for submit_id {$this->submit_id}");
        $sub = \App\Models\Submit::find($this->submit_id);
        $sub->accept_id = $this->accept_id;
        $sub->save();
        $this->isEditing = false;
        $this->render();
    }
}
