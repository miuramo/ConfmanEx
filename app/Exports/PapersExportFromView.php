<?php

namespace App\Exports;

use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;

class PapersExportFromView extends AbstractExportFromView
{
    protected array $targets;
    public function __construct($tgt)
    {
        $this->targets = $tgt;
    }
    public function view(): View
    {
        $all = Paper::whereIn('category_id', $this->targets)->get();
        $roles = auth()->user()->roles;
        $enqans = EnqueteAnswer::getAnswers();

        return view('components.admin.papertable')->with(compact("all", "roles", "enqans"));
    }

    
}
