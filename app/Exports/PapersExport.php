<?php

namespace App\Exports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PapersExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Paper::all();
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
    public function map($paper): array
    {
        return [
            $paper->category_id,
            $paper->id,
            $paper->id_03d(),
            $paper->title,
            $paper->paperowner->name,
            $paper->paperowner->affil,
            $paper->paperowner->email,
            str_replace("\n", "\r\n", $paper->contactemails),
        ];
    }
}
