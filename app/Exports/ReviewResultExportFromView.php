<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use App\Models\Submit;
use Illuminate\Contracts\View\View;

class ReviewResultExportFromView extends AbstractExportFromView
{
    protected Category $target;
    public function __construct($tgt)
    {
        $this->target = $tgt;
    }
    public function view(): View
    {
        $cat = $this->target;
        $subs = Submit::with('paper')->where('category_id', $cat->id)->orderBy('score', 'desc')->get();
        return view('components.review.resultmap')->with(compact("subs", "cat"));
    }

    
}
