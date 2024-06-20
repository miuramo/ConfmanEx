<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'submit_id',
        'paper_id',
        'user_id',
        'category_id',
        'ismeta',
        'status',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    /**
     * 査読割り当て
     * status 2がメタ 1が通常 0が解除
     */
    public static function review_assign($paper_id, $user_id, $status)
    {
        $paper = Paper::find($paper_id);
        $status = intval($status);
        if ($status > 0) {
            // 既存のデータがあれば、それを読み取って修正する
            $rev = Review::where('user_id', $user_id)->where('paper_id', $paper_id)->first();
            if ($rev != null) {
                $rev->submit_id = $paper->submits->first()->id;
                $rev->category_id = $paper->category_id;
                $rev->ismeta = ($status == 2);
                $rev->save();
            } else { //データがないので作成する
                Review::firstOrCreate([
                    'submit_id' => $paper->submits->first()->id,
                    'paper_id' => $paper_id,
                    'user_id' => $user_id,
                    'category_id' => $paper->category_id,
                    'ismeta' => ($status == 2),
                ]);
            }
        } else {
            $dat = Review::where([['user_id', $user_id], ['paper_id', $paper_id]])->get();
            foreach ($dat as $r) {
                $r->delete();
            }
        }
    }

    /**
     * 数をしらべる。( field = paper_id or user_id )
     */
    public static function revass_stat($catid,$field="user_id")
    {
        $tmp = Review::select(DB::raw("count(id) as count, {$field}, ismeta"))
        ->where('category_id', $catid)
        ->groupBy($field)
        ->groupBy("ismeta")
        ->orderBy($field)
        ->get();
        $ret = [];
        foreach($tmp as $n=>$t){
            $ret[$t->{$field}][$t->ismeta] = $t->count;
        }
        return $ret;
    }
    public static function revass_stat_allcategory()
    {
        $field = "user_id";
        $tmp = Review::select(DB::raw("count(id) as count, {$field}, ismeta"))
        ->groupBy($field)
        ->groupBy("ismeta")
        ->orderBy($field)
        ->get();
        $ret = [];
        foreach($tmp as $n=>$t){
            $ret[$t->{$field}][$t->ismeta] = $t->count;
        }
        return $ret;
    }

    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = rev
     */
    public static function arr_pu_rev()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = $a;
        }
        return $ret;
    }
    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = status(2:meta 1:normal)
     */
    public static function arr_pu_status()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->paper_id][$a->user_id] = ($a->ismeta) ? 2 : 1;
        }
        return $ret;
    }
    /**
     * ネストした配列で返す
     * arr[paper_id][user_id] = star span
     */
    public static function arr_pu_star()
    {
        $ret = [];
        $colors = ["teal", "cyan", "red"];
        foreach (Review::all() as $a) {
            $status = ($a->ismeta) ? 2 : 1;
            $span = "<span class=\"text-2xl text-{$colors[$status]}-500\">★</span>";
            $ret[$a->paper_id][$a->user_id] = $span;
        }
        return $ret;
    }

    /**
     * 査読割り当ての前に、全査読者の利害を抽出する
     */
    public static function extractAllCoAuthorRigais()
    {
        // 査読者とメタ査読者
        $roles = Role::where("name", "like", "%reviewer")->get();
        foreach ($roles as $role) {
            foreach ($role->users as $revu) {
                // 自著分、共著分については、さきにRevConflictを作成しておく
                $author_papers = Paper::where("owner", $revu->id)->get();
                foreach ($author_papers as $p) {
                    $revcon = RevConflict::firstOrCreate([
                        'user_id' => $revu->id,
                        'paper_id' => $p->id,
                        'bidding_id' => 1, // 1が共著者利害
                    ]);
                }
                $user = User::find($revu->id);
                foreach ($user->coauthor_papers() as $p) {
                    $revcon = RevConflict::firstOrCreate([
                        'user_id' => $revu->id,
                        'paper_id' => $p->id,
                        'bidding_id' => 1, // 1が共著者利害
                    ]);
                }
            }
        }
    }

    // status 0は未回答、1は回答中、2は完了 を更新する
    public function validateOneRev()
    {
        $finish_vpids = Score::where('review_id', $this->id)->whereNotNull('valuestr')->get()->pluck('viewpoint_id')->count();
        $all_vpids = Viewpoint::where('category_id', $this->category_id)->count();
        // ->whereNotIn('id', $finish_vpids)->
        if ($finish_vpids ==0){
            $this->status = 0;
        } else if ($finish_vpids == $all_vpids){
            $this->status = 2;
        } else {
            $this->status = 1;
        }
        $this->save();
    }

    /**
     * 未回答があると $rev->scores は抜けてしまうので、viewpoints をつかってKey->value として確実に配列で返す。
     */
    public function scores_and_comments()
    {
        $aryscores = $this->scores->pluck("valuestr", "viewpoint_id")->toArray();
        $vps = Viewpoint::where('category_id', $this->category_id)->orderBy('orderint')->get();
        $ret = [];
        foreach($vps as $vp){
            $ret[$vp->desc] = (isset($aryscores[$vp->id])) ? $aryscores[$vp->id] : "(未入力)";
        }
        return $ret;
    }

    /**
     * すべてのstatusを更新する（査読未完了のチェックの前に実行する）
     */
    public static function validateAllRev()
    {
        $all = Review::all();
        foreach($all as $rev){
            $rev->validateOneRev();
        }
    }
}
