<?php

namespace App\Exports;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;

class EnqExportFromView extends AbstractExportFromView
{
    protected Enquete $enq;
    public function __construct($e)
    {
        $this->enq = $e;
    }
    public function view(): View
    {
        $papers = Paper::with('paperowner')->with('submits')->orderBy('category_id')->orderBy('id')->get();
        $enq = $this->enq;
        $enq_id = $this->enq->id; 
        $enqans = EnqueteAnswer::where('enquete_id', $enq_id)->orderBy('paper_id')->get();
        if ($enq->withpaper){
            return view('components.admin.enqtable')->with(compact("enq","enqans","papers"));    
        } else {
            return view('components.admin.enqtable_nopaper')->with(compact("enq","enqans"));
        }
    }

    
}
