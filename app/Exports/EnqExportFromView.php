<?php

namespace App\Exports;

use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EnqExportFromView implements FromView, ShouldAutoSize, WithHeadings
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
        if ($enq->withPaper){
            return view('components.admin.enqtable')->with(compact("enq","enqans","papers"));    
        } else {
            return view('components.admin.enqtable_nopaper')->with(compact("enq","enqans"));
        }
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
