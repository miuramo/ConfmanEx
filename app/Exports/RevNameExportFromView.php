<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RevNameExportFromView implements FromView, ShouldAutoSize, WithHeadings
{
    protected int $cat_id;
    public function __construct($e)
    {
        $this->cat_id = $e;
    }
    public function view(): View
    {
        return view('components.review.revname_table')->with("cat_id", $this->cat_id);
    }

    public function headings(): array
    {
        return [
            'id',
            'title',
            'primary',
            '査読者1',
            '査読者2',
            '査読者3',
            '',
            '利害',
            '利害',
            '利害',
            '利害',
        ];
    }
}
