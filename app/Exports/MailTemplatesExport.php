<?php

namespace App\Exports;

use App\Models\MailTemplate;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MailTemplatesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $mt_ids = [];
    public function __construct($mt_ids)
    {
        $this->mt_ids = $mt_ids;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return MailTemplate::whereIn('id', $this->mt_ids)->get();
    }

    public function headings(): array
    {
        $fields = Schema::getColumnListing('mail_templates');
        return $fields;
    }

}
