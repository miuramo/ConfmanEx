<?php

namespace App\Exports;

use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\Paper;
use App\Models\Submit;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * 情報学広場 ShouldAutoSize,
 */
class PapersExport4Hiroba implements FromView, WithHeadings
{
    // protected array $targets;
    public function __construct()
    {
        // $this->targets = $tgt;
    }
    public function view(): View
    {
        $submits = Submit::with("accept")->with("paper")->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->whereIn("category_id", [1, 2, 3])->orderBy("orderint", "asc")->get();
        // 順番=orderint  submission=paper_id

        // pagenum array
        $pagenums = File::pluck("pagenum", "id")->toArray();
        $heads = $this->headings();

        return view('components.paper.excel_hiroba')->with(compact("submits","pagenums","heads"));
    }

    public function headings(): array
    {
        return [
            '順番', 'submission', '文献種類', '論文タイトル', '言語',
            'キーワード', '公開日', '論文タイトル英語', 'その他タイトル', '著者所属',
            '著者所属英語', '著者名', '著者名英語', '論文抄録', '論文抄録英語',
            '研究会名', 'ファイル名', 'ファイル公開日', '非会員価格', '会員価格',
            'ライセンス表記', '書誌レコードID', '雑誌名', '巻', '号',
            '開始ページ', '終了ページ', '発行年月日', 'ページ数'
        ];
    }
}
