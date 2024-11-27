<?php

namespace App\Imports;

use App\Models\MailTemplate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MailTemplatesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new MailTemplate([
            'user_id' => auth()->id(),
            'from' => $row['from'],
            'to' => $row['to'],
            'cc' => $row['cc'],
            'bcc' => $row['bcc'],
            'subject' => $row['subject'],
            'body' => $row['body'],
            'name' => $row['name'],
            'category_id' => $row['category_id'],
            'lastsent' => null,
            'created_at' => now(),
            'updated_at' => null, // nullにしても、結局のところ、created_atと同じになる
            //
        ]);
    }
}
