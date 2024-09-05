<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use App\Models\Role;
use App\Models\Submit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BiddingResultExportFromView implements FromView, ShouldAutoSize, WithHeadings
{
    protected Category $cat;
    protected Role $role;
    public function __construct($_cat, $_role)
    {
        $this->cat = $_cat;
        $this->role = $_role;
    }
    public function view(): View
    {
        $reviewers = $this->role->users;
        // $roles = Role::where("name", "like", "%reviewer")->get();
        $papers = $this->cat->paperswithpdf;
        // $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        $cat = $this->cat;
        $role = $this->role;
        return view('components.role.revmap', ["role" => $role, "cat" => $cat])->with(compact("reviewers", "papers"));
    }

    public function headings(): array
    {
        return [
            'cat',
            'id',
            'id03d',
            'title',
            'owner',
            'owneraffil',
            'owneremail',
            'contactemails',
        ];
    }
}
