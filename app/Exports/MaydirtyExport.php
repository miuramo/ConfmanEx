<?php

namespace App\Exports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaydirtyExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Paper::orderBy('category_id', 'asc')->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        $koumoku = \App\Models\BibEntry::where('is_required', 1)->where('for_manage', 0)->orderBy('display_order')->pluck('name_jp', 'key')->toArray();
        $koumoku['pid'] = 'PaperID';
        $koumoku['catid'] = 'カテゴリーID';

        return array_keys($koumoku);
    }

    public function map($paper): array
    {
        $manda_bibs = Paper::mandatory_bibs($paper->category_id);
        // $paper->maydirty は JSON 文字列だが、自動的に配列になる
        $maydirty = $paper->maydirty;
        // key=true の項目だけ抽出する
        $ret['pid'] = $paper->id;
        $ret['catid'] = $paper->category_id;
        $koumoku = \App\Models\BibEntry::where('is_required', 1)->where('for_manage', 0)->orderBy('display_order')->pluck('name_jp', 'key')->toArray();
        foreach ($koumoku as $field => $label) {
            // if ($field == 'pid') {
            //     continue;
            // }
            // if ($field == 'catid') {
            //     continue;
            // }
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
