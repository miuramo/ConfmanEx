<?php

namespace App\Exports;

use App\Models\Role;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoleMembersExportFromView implements FromView, ShouldAutoSize, WithHeadings
{
    protected Role $role;
    public function __construct($ro)
    {
        $this->role = $ro;
    }
    public function view(): View
    {
        $users = $this->role->users;
        $role = $this->role;
        $roles = Role::orderBy("id")->get();
        return view('components.role.members')->with(compact("users", "role"));
    }

    public function headings(): array
    {
        return [
            'name',
            'affil',
            'email',
        ];
    }
}
