<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'leadtext',
    ];

    /**
     * PDFファイルがないものも含めて返す。
     */
    public function papers()
    {
        return $this->hasMany(Paper::class,'category_id')->orderBy('id');
    }
    /**
     * PDFファイルがあるものだけを返す。
     */
    public function paperswithpdf()
    {
        return $this->hasMany(Paper::class,'category_id')->whereNotNull('pdf_file_id')->orderBy('id');
    }

    public static function spans()
    {
        $all = Category::all();
        $spans = [];
        foreach($all as $c){
            $spans[$c->id] = "<span class=\"inline-block text-{$c->color}-500 bg-{$c->bgcolor}-200 text-md p-2 rounded-xl font-bold  dark:bg-{$c->color}-500 dark:text-{$c->bgcolor}-200\">{$c->name}</span>";
        }
        return $spans;
    }

    /**
     * 新規投稿受付ボタン
     */
    public function isOpen(){
        return Enquete::checkdayduration($this->openstart, $this->openend);
    }

    public function is_accept_pdf(){
        return Enquete::checkdayduration($this->pdf_accept_start, $this->pdf_accept_end);
    }

    /**
     * 投稿数が設定の上限(upperlimit)を超えたらfalse
     */
    public function isnotUpperLimit(){
        if ($this->upperlimit == 0) return true;
        $papercount = Paper::where("category_id", $this->id)->count();
        return ($papercount < $this->upperlimit );
    }

    /**
     * 査読結果を表示するかどうか
     */
    public static function isShowReview($cat_id){
        $canshow = false;
        $revlist = Category::select('id', 'status__revlist_on')->get()->pluck('status__revlist_on', 'id')->toArray();
        if (!auth()->user()->can('role', 'pc')) {
            if (auth()->user()->can('role_any', 'reviewer|metareviewer') && $revlist[$cat_id]) {
                $canshow = true;
            } else {
            }
        } else {
            $canshow = true;
        }
        return $canshow;
    }

}
