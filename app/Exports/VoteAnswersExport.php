<?php

namespace App\Exports;

use App\Models\VoteAnswer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VoteAnswersExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return VoteAnswer::all();
    }

    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'vote_id',
            'submit_id',
            'booth',
            'valid (1:一般, 2:学生)',
            'comment',
            'token',
            'created_at',
            'updated_at',
        ];
    }
    public function map($vans): array
    {
        return [
            $vans->id,
            $vans->user_id,
            $vans->vote_id,
            $vans->submit_id,
            $vans->booth,
            $vans->valid,
            $vans->comment,
            $vans->token,
            $vans->created_at,
            $vans->updated_at,
        ];
    }
}
