<?php

namespace App\Exports;

use App\Models\VoteAnswer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VoteAnswersExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    public int $vote_id;
    public function __construct(int $vote_id = 0)
    {
        $this->vote_id = $vote_id;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if ($this->vote_id == 0) return VoteAnswer::where('valid', 1)->get();
        else return VoteAnswer::where('valid', 1)->where('vote_id', $this->vote_id)->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'vote_id',
            'vote_item_id',
            'submit_id',
            'booth',
            'valid',
            // 'valid (1:一般, 2:学生)',
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
            $vans->vote_item_id,
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
