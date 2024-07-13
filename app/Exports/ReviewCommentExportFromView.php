<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use App\Models\Submit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReviewCommentExportFromView implements FromView, ShouldAutoSize, WithHeadings
{
    protected Category $category;
    protected int $scoreonly;

    public function __construct($tgt, $sonly = 0)
    {
        $this->category = $tgt;
        $this->scoreonly = $sonly;
    }
    public function view(): View
    {
        $cat = $this->category;
        $subs = Submit::with('paper')->where('category_id', $cat->id)->orderBy('score', 'desc')->get();
        $cat_id = $cat->id;
        $scoreonly = $this->scoreonly;
        return view('components.review.pccommentmap')->with(compact("subs", "cat_id", "scoreonly"));
    }

    public function headings(): array
    {
        return [];
    }
}
