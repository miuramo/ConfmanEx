<?php

namespace App\Exports;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;

class MultiEnqExportFromView extends AbstractExportFromView
{
    protected array $enq_ids;
    protected bool $with_paper;
    public function __construct($e, $with_paper = false)
    {
        $this->enq_ids = $e;
        $this->with_paper = $with_paper;
    }
    public function view(): View
    {
        $enq_ids = $this->enq_ids;
        if ($this->with_paper) {
            return view('components.admin.multienq_table')->with(compact("enq_ids"));
        } else {
            return view('components.admin.multienq_table_regist')->with(compact("enq_ids"));
        }
    }

    
}
