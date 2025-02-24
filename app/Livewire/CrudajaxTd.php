<?php

namespace App\Livewire;

use Livewire\Component;

class CrudajaxTd extends Component
{
    public $dat;
    public $id;
    public $field;
    public $type;
    public $value;
    public bool $isEditing = false;
    public $oldValue;

    public function mount()
    {
        $this->value = $this->dat->{$this->field};
    }
    public function render()
    {
        return view('livewire.crudajax-td');
    }
    public function updatedValue()
    {
        if ($this->type == 'tinyint') {
            $this->dat->{$this->field} = $this->value;
            $this->dat->save();
        }
    }
    public function startEditing()
    {
        $this->isEditing = true;
        $this->oldValue = $this->value;
        $this->dispatch('startEditing');
    }
    public function save()
    {
        $this->isEditing = false;
        $this->dat->{$this->field} = $this->value;
        $this->dat->save();
    }
    public function cancel()
    {
        $this->isEditing = false;
        $this->value = $this->oldValue;
    }

}
