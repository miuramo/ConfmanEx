<?php

namespace App\Exports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaydirtyExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    public $koumoku = [
        'pid' => 'PaperID',
        'catid' => 'カテゴリーID',
        'title' => '和文タイトル',
        'abst' => '和文アブストラクト',
        'keyword' => '和文キーワード',
        'authorlist' => '和文著者名',
        'etitle' => '英文Title',
        'eabst' => '英文Abstract',
        'ekeyword' => '英文Keyword',
        'eauthorlist' => '英文Author(s)'
    ];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Paper::orderBy('category_id', 'asc')->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return array_keys($this->koumoku);
    }

    public function map($paper): array
    {
        $manda_bibs = Paper::mandatory_bibs($paper->category_id);
        // $paper->maydirty は JSON 文字列だが、自動的に配列になる
        $maydirty = $paper->maydirty;
        // key=true の項目だけ抽出する
        $ret['pid'] = $paper->id;
        $ret['catid'] = $paper->category_id;
        foreach ($this->koumoku as $field => $label) {
            if ($field == 'pid') {
                continue;
            }
            if ($field == 'catid') {
                continue;
            }
            if (!isset($manda_bibs[$field])) {
                // 必須項目でなければスキップ
                $ret[$field] = "__skip__";
                continue;
            }
            $ret[$field] = $this->getFieldValue($maydirty, $field);
        }

        return $ret;
    }

    protected function getFieldValue($maydirty, $field)
    {
        if (isset($maydirty[$field])) {
            if ($maydirty[$field] === 'true') {
                return 0;
            }
        }
        return 1;
    }
}
