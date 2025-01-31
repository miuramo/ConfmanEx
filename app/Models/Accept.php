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

    /**
     * 与えられたカテゴリにおいて、判定で使用されているaccept_idを key & value の両方で返す。
     * key にしているのは、isset() での判定ができるため。
     */
    public static function used_accepts($cat_id = 1)
    {
        $res = DB::select("select distinct accept_id from submits where category_id = ? " +
            " and paper_id in (select id from papers where deleted_at is null) order by accept_id", [$cat_id]);
        $ret = [];
        foreach ($res as $r) {
            $ret[$r->accept_id] = $r->accept_id;
        }
        return $ret;
    }

    public static function acc_status($include_paperid = false)
    {
        $fs = [
            "papers.category_id as origcat",
            "submits.category_id as hanteicat",
            "submits.accept_id",
            // "accepts.name as acceptname",
            // "accepts.judge",
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

    public static function random_pids_for_each_accept($cat_id = 1)
    {
        $paperlist = Accept::acc_status(true);
        $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $show = [];
        $showpid = [];
        $metarev = [];
        $rev = [];
        foreach ($paperlist[$cat_id][$cat_id] as $accid => $pids) {
            $random_idx = array_rand($pids);
            $random_pid = $pids[$random_idx];
            $random_paper = Paper::find($random_pid);
            $random_paper_owner = $random_paper->owner;
            $showpid[$accid] = sprintf('%03d', $random_pid);
            $show[$accid] = $random_paper_owner;
            $random_sub = Submit::where('paper_id', $random_pid)->get()->first();
            $metarev[$accid] = Review::where('submit_id', $random_sub->id)
                ->where('ismeta', 1)
                ->get()
                ->first();
            $rev[$accid] = Review::where('submit_id', $random_sub->id)
                ->where('ismeta', 0)
                ->get()
                ->first();
        }
        return [
            'accepts' => $accepts,
            'show' => $show,
            'showpid' => $showpid,
            'metarev' => $metarev,
            'rev' => $rev,
        ];
    }

    public static function nodes()
    {
        $nodes = [];
        $links = [];
        $conftitle_base = Setting::findByIdOrName('CONFTITLE_BASE', 'value');
        if ($conftitle_base == 'インタラクション') {
            $links[] = [
                "source" => 'h1a1',
                "target" => 'h1a6',
            ];
            // $links[] = [
            //     "source" => 'h2a3',
            //     "target" => 'h2a4',
            // ];
            // $links[] = [
            //     "source" => 'h3a3',
            //     "target" => 'h3a4',
            // ];

        } else if ($conftitle_base == 'WISS') {
            $links[] = [
                "source" => 'h3a3',
                "target" => 'h3a4',
            ];
            $links[] = [
                "source" => 'h1a1',
                "target" => 'h1a2',
            ];
            $links[] = [
                "source" => 'h1a2',
                "target" => 'h1a21',
            ];
        }

        $accepts = Accept::select('shortname', 'id')->get()->pluck('shortname', 'id')->toArray();
        $acc_judges = Accept::select('judge', 'id')->get()->pluck('judge', 'id')->toArray();
        $cats = Category::select('id', 'shortname')->get()->pluck('shortname', 'id')->toArray();

        // 二重につくらないようにする
        $alerady = [];
        // 次に、acceptのノードを作成
        $stats = Accept::acc_status();
        foreach ($stats as $st) {
            if ($st->accept_id == 20) {
                continue;
            }
            $id = 'h' . $st->hanteicat . 'a' . $st->accept_id;
            if (isset($alerady[$id])) {
                continue;
            }
            $nodes[] = [
                "id" => $id,
                "accid" => $st->accept_id,
                "label" => $cats[$st->hanteicat] . '-' . $accepts[$st->accept_id],
                "type" => "A",
                "width" => 80,
                "shape" => "ellipse",
                "color" => "lightgreen",
            ];
            $alerady[$id] = 1;
            // $links[] = ['source' => 'c'.$st->hanteicat, 'target' => $id];
        }
        // 最後に、paperのノードを作成
        $pids = Accept::acc_status(true);
        foreach ($pids as $origcat => $ooo) {
            foreach ($ooo as $hanteicat => $hhh) {
                foreach ($hhh as $accept_id => $ppp) {
                    if ($acc_judges[$accept_id] == 0) {
                        continue;
                    }
                    $id = 'h' . $hanteicat . 'a' . $accept_id;
                    foreach ($ppp as $pid) {
                        if (isset($alerady[$pid])) {
                            $links[] = ['source' => $id, 'target' => 'p' . $pid];
                            continue;
                        } else {
                            $nodes[] = [
                                "id" => 'p' . $pid,
                                "accid" => "--",
                                "label" => $pid,
                                "type" => "B",
                                "width" => 30,
                                "shape" => "ellipse",
                                "color" => "lightyellow",
                            ];
                            $alerady[$pid] = 1;
                        }
                        $links[] = ['source' => $id, 'target' => 'p' . $pid];
                    }
                }
            }
        }
        return [
            "nodes" => $nodes,
            "links" => $links,
        ];
    }
}
