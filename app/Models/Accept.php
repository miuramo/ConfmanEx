<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Accept extends Model
{
    use HasFactory;

    public function submits()
    {
        return $this->belongsTo(Submit::class); //逆はhasOne
    }

    public static function acc_status($include_paperid = false)
    {
        $fs = [
            "papers.category_id as origcat",
            "submits.category_id as hanteicat",
            "submits.accept_id",
            // "accepts.name as acceptname",
            "accepts.judge",
        ];
        if ($include_paperid) { // paper_idを含める
            $fs[] = "submits.paper_id";
            $res = DB::select("select " . implode(", ", $fs) . " " .
                "from submits left join accepts on submits.accept_id = accepts.id " .
                "left join papers on submits.paper_id = papers.id " .
                "where papers.deleted_at is null " .
                "order by origcat, hanteicat, submits.accept_id, submits.paper_id ");
            $ret = [];
            foreach ($res as $r) {
                $ret[$r->origcat][$r->hanteicat][$r->accept_id][] = $r->paper_id;
            }
            return $ret;
        } else { // paper_idを含めない。cnt を含める
            $fs[] = "count(submits.id) as cnt";
            $res = DB::select("select " . implode(", ", $fs) . " " .
                "from submits left join accepts on submits.accept_id = accepts.id " .
                "left join papers on submits.paper_id = papers.id " .
                "where papers.deleted_at is null " .
                "group by origcat, hanteicat, submits.accept_id " .
                "order by origcat, hanteicat, submits.accept_id ");
            return $res;
        }
    }
}
