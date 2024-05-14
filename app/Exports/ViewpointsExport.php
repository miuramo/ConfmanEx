<?php

namespace App\Exports;

use App\Models\Viewpoint;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ViewpointsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Viewpoint::all();
    }

    public function headings(): array
    {
        $fields = Schema::getColumnListing('viewpoints');
        return $fields;
    }

}
