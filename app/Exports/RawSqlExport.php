<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RawSqlExport implements FromCollection, WithHeadings
{
    protected string $sql;
    protected array $bindings;
    // protected array $headings;

    public function __construct(
        string $sql,
        array $bindings = [],
        // array $headings = []
    ) {
        $this->sql = $sql;
        $this->bindings = $bindings;
        // $this->headings = $headings;
    }

    public function collection(): Collection
    {
        // select は array<object> を返す
        $rows = DB::select($this->sql, $this->bindings);

        // Excel 用に Collection + array に変換
        return collect($rows)->map(function ($row) {
            return (array) $row;
        });
    }

    public function headings(): array
    {
        $rows = DB::select($this->sql, $this->bindings);
        if (empty($rows)) {
            return [];
        }
        return array_keys((array) $rows[0]);
    }
    // public function headings(): array
    // {
    //     // 明示的に指定された場合のみ使う
    //     if (!empty($this->headings)) {
    //         return $this->headings;
    //     }

    //     // データから自動生成（1行目のキー）
    //     return [];
    // }
}
