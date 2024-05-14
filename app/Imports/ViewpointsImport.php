<?php

namespace App\Imports;

use App\Models\Viewpoint;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ViewpointsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Viewpoint([
            'category_id' => $row['category_id'],
            'orderint' => $row['orderint'],
            'name' => $row['name'],
            'desc' => $row['desc'],
            'content' => $row['content'],
            'contentafter' => $row['contentafter'],
            'forrev' => $row['forrev'] ?? true,
            'formeta' => $row['formeta'] ?? false,
            'weight' => $row['weight'] ?? 0,
            'doReturn' => $row['doReturn'] ?? false,
            'doReturnAcceptOnly' => $row['doReturnAcceptOnly'] ?? false,
            //
        ]);
    }
}
