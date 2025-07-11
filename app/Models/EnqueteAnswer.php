<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EnqueteAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquete_id',
        'enquete_item_id',
        'user_id',
        'paper_id',
        'value',
        'valuestr',
    ];

    public function item()
    {
        return $this->belongsTo(EnqueteItem::class, 'enquete_item_id');
    }
    /**
     * PDFファイルがないものも含めて返す。
     */
    public function papers()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }


    // アンケート showonpaperindex
    // [paperid][enqid][name1] = value1
    // [paperid][enqid][name2] = value2
    public static function getAnswers()
    {
        $showonEnq = Enquete::where("showonpaperindex", true)->get()->pluck('id')->toArray();

        $all = EnqueteAnswer::with('item')->whereIn('enquete_id', $showonEnq)
            ->orderBy("paper_id")->orderBy("enquete_id")->orderBy("enquete_item_id")->get();
        $ret = [];
        foreach ($all as $ea) {
            if (isset($ea->item)) $ret[$ea->paper_id][$ea->enquete_id][$ea->item->name] = $ea->valuestr;
        }
        return $ret;
    }



    /**
     * デモ希望としているアンケート回答数を、採択状況ごとに分けてカウントする
     */
    public static function demoCount(){
        $demoenqitem = EnqueteItem::where("name", "demoifaccepted")->first();
        if ($demoenqitem != null) {
            $demoenqitemid = $demoenqitem->id;
            // EnqueteAnswerのうち、demoenqitemid に "はい" と答えているものをカウント
            $res = EnqueteAnswer::where("enquete_item_id", $demoenqitemid)->where("valuestr", "はい")->
            whereIn("paper_id", Paper::select("id")->whereNull("deleted_at"))->count();
            return $res;
        }
        return 0;
    }
    // すべてのカテゴリについて、デモ希望をだしているPaperIDのリストを返す 例:[9,24,29,31,33]
    // ただし、Paper->deleted_at が null であるものに限る
    public static function demoPaperIDs(){
        $demoenqitem = EnqueteItem::where("name", "demoifaccepted")->first();
        if ($demoenqitem != null) {
            $demoenqitemid = $demoenqitem->id;
            // EnqueteAnswerのうち、demoenqitemid に "はい" と答えているものをカウント
            $res = EnqueteAnswer::select("paper_id")->where("enquete_item_id", $demoenqitemid)->where("valuestr", "はい")->
            whereIn("paper_id", Paper::select("id")->whereNull("deleted_at"))->
            orderBy("paper_id")->get()->pluck("paper_id")->toArray(); 
            return $res;
        }
        return [];
    }
    // すべてのカテゴリについて、デモ希望をだしているPaperID=>CatIDの配列を返す 例:[9=>1,24=>1,29=>3,31=>3,33=>1]
    public static function demoPaperIDs_CatID(){
        $demoPaperIDs = self::demoPaperIDs();
        $res = Paper::select("id", "category_id")->whereIn("id", $demoPaperIDs)->orderBy("id")->get()->pluck("category_id", "id")->toArray();
        return $res;
    }
    // 上記の結果を、カテゴリごとに分けて返す 例:[1=>[0=>9,1=>24,2=>33],3=>[0=>29,1=>31]]
    public static function demoPaperIDs_eachCat(){
        $demoPaperIDs = self::demoPaperIDs_CatID();
        $res = [];
        foreach($demoPaperIDs as $pid => $cid){
            if (!isset($res[$cid])) $res[$cid] = [];
            $res[$cid][] = $pid;
        }
        ksort($res);
        return $res;
    }
    // さらに、採択状況によって分ける
    // group by submits.accept_id, papers.category_id

    public static function demoPaperIDs_eachCat_eachAccID(){
        $demoPaperIDs = self::demoPaperIDs_CatID();
        if (count($demoPaperIDs) == 0) return ["ary"=>[], "cat"=>[], "acc"=>[]];

        $fs = ["submits.category_id", "submits.accept_id", "submits.paper_id"];
        $sql1 = "select ".implode(",", $fs). ", accepts.name, categories.name as catname";
        $sql1 .= " from submits left join accepts on submits.accept_id = accepts.id";
        $sql1 .= " left join categories on submits.category_id = categories.id";
        $sql1 .= " where paper_id in (".implode(",", array_keys($demoPaperIDs)).")";
        $sql1 .= " order by " . implode(",", $fs);
        $cols = DB::select($sql1);
        $ary = []; // category_id, accept_id
        $cats = [];
        $accs = [];
        foreach ($cols as $c) {
            $ary[$c->category_id][$c->accept_id][] = $c->paper_id;
            $cats[$c->category_id] = $c->catname;
            $accs[$c->accept_id] = $c->name;
        }
        return ["ary"=>$ary, "cat"=>$cats, "acc"=>$accs];
    }

}
