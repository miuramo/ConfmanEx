<?php

namespace App\Livewire;

use Livewire\Component;

class RegistAdminSearch extends Component
{
    public string $search = '';
    public $users = [];
    public $regD = [];
    public function updatedSearch()
    {
        if ($this->search == '') {
            $this->users = [];
        } else {
            $this->users = \App\Models\User::where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('affil', 'like', "%{$this->search}%")
                ->limit(10)
                ->get()->keyBy('id');
            $this->regD = \App\Models\Regist::whereIn('user_id', $this->users->keys())->get()->keyBy('user_id');
        }
    }
    public function resetSearch()
    {
        $this->search = '';
        $this->users = [];
    }

    public function render()
    {
        return view('livewire.regist-admin-search');
    }
}
