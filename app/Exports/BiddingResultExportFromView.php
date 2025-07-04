<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use App\Models\Role;
use App\Models\Submit;
use Illuminate\Contracts\View\View;

class BiddingResultExportFromView extends AbstractExportFromView
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

    
}
