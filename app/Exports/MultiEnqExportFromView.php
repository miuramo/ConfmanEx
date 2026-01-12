<?php

namespace App\Exports;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;

class MultiEnqExportFromView extends AbstractExportFromView
{
    protected array $enqids;
    public function __construct($e)
    {
        $this->enqids = $e;
    }
    public function view(): View
    {
        $papers = Paper::with('paperowner')->with('submits')->orderBy('category_id')->orderBy('id')->get();
        $enqids = $this->enqids;
        return view("enquete.answers_multienq")->with(compact("enqs", "enqans", "papers", "enq_ids"));
        return view('components.admin.multienq_table')->with(compact("enq","enqans","papers"));    
    }

    
}
