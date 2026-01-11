<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InteractiveBoothExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
     public $koumoku = [
        'catid' => 'カテゴリーID',
        'id' => 'PaperID',
        'id03d' => 'PaperID(03d)',
        'accept' => 'AcceptStatus', // from accept_id
        'premium' => 'プレミアム',    // from accept_id 
        'type' => 'インタラクティブ種別',
        'memo' => '委員メモ',
        'title' => '和文タイトル',
        'owner' => '投稿者氏名',
    ];
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
    }
    public function headings(): array
    {
        return array_keys($this->koumoku);
    }
    public function map($submit): array
    {
        //
        return [];
    }
}
