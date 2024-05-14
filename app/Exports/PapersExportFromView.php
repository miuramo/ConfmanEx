<?php

namespace App\Exports;

use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PapersExportFromView implements FromView, ShouldAutoSize, WithHeadings
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
