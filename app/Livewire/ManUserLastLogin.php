<?php

namespace App\Livewire;

use App\Http\Middleware\LogAccess;
use App\Models\LogAccess as ModelsLogAccess;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ManUserLastLogin extends Component
{
    public $count_null_lastlogin = 0;

    public function get_null_lastlogin_count()
    {
        $this->count_null_lastlogin = ModelsLogAccess::update_last_login(10);
    }

    public function mount()
    {
        $this->get_null_lastlogin_count();
    }
    public function render()
    {
        return view('livewire.man-user-last-login');
    }
}
