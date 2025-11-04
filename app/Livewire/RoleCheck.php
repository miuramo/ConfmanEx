<?php

namespace App\Livewire;

use App\Models\Role;
use Livewire\Component;

class RoleCheck extends Component
{
    public Role $role;
    public bool $invitemode = false;
    public function mount(Role $roleobj)
    {
        $this->role = $roleobj;
    }

    public function render()
    {
        return view('livewire.role-check');
    }

    public function open_invite()
    {
        if (auth()->user()->can('role', $this->role)) {
            $this->invitemode = true;
        }
    }
    public function close_invite()
    {
        $this->invitemode = false;
    }

    public string $search = '';
    public $users = [];
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
        }
    }
    public function resetSearch()
    {
        $this->search = '';
        $this->users = [];
    }
    public function addUser($user_id)
    {
        $this->role->users()->attach($user_id);
        $this->resetSearch();
    }
}
