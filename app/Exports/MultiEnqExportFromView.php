<?php

namespace App\Exports;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;

class MultiEnqExportFromView extends AbstractExportFromView
{
    protected array $enq_ids;
    public function __construct($e)
    {
        $this->enq_ids = $e;
    }
    public function view(): View
    {
        $enq_ids = $this->enq_ids;
        return view('components.admin.multienq_table')->with(compact("enq_ids"));    
    }

    
}
