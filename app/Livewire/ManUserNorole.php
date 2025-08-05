<?php

namespace App\Livewire;

use Livewire\Component;

class ManUserNorole extends Component
{
    public $norole_users = [];

    public function get_norole_users()
    {
        // ロールが設定されていないユーザを取得

        $this->norole_users = \App\Models\User::whereNotIn('id', function ($query) {
            $query->select('user_id')->from('role_user');
        })->get();
    }
    public function mount()
    {
        $this->get_norole_users();
    }
    public function softdelete_norole_nopaper_users()
    {
        foreach ($this->norole_users as $user) {
            if (count($user->papers) == 0) {
                info("Soft deleting user {$user->id} ({$user->name}) with no roles and no papers.");
                $user->delete();
            }
        }
        $this->get_norole_users();
    }
    public function render()
    {
        return view('livewire.man-user-norole');
    }
}
