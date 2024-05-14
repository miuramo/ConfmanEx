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

class ReviewResultExportFromView implements FromView, ShouldAutoSize, WithHeadings
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
