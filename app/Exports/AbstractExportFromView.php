<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

abstract class AbstractExportFromView implements FromView, ShouldAutoSize, WithHeadings
{
    abstract public function view(): View;

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
