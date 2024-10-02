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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * この査読のトークンを生成（査読者同士の参照用）
     */
    public function token()
    {
        return sha1($this->id . $this->user_id . $this->paper_id . $this->category_id);
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
            DB::transaction(function () use ($paper, $user_id, $status) {
                // 既存のデータがあれば、それを読み取って修正する
                $rev = Review::where('user_id', $user_id)->where('paper_id', $paper->id)->first();
                if ($rev != null) {
                    $rev->submit_id = $paper->submits->first()->id;
                    $rev->category_id = $paper->category_id;
                    $rev->ismeta = ($status == 2);
                    $rev->save();
                } else {
                    Review::firstOrCreate([
                        'submit_id' => $paper->submits->first()->id,
                        'paper_id' => $paper->id,
                        'user_id' => $user_id,
                        'category_id' => $paper->category_id,
                        'ismeta' => ($status == 2),
                    ]);
                }
            });
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
    public static function revass_stat($catid, $field = "user_id")
    {
        $tmp = Review::select(DB::raw("count(id) as count, {$field}, ismeta"))
            ->where('category_id', $catid)
            ->groupBy($field)
            ->groupBy("ismeta")
            ->orderBy($field)
            ->get();
        $ret = [];
        foreach ($tmp as $n => $t) {
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
        foreach ($tmp as $n => $t) {
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
        // 自分が　ismeta なら、formetaの項目を数える。そうでなければ、forrev の項目を数える。
        $all_vpids = Viewpoint::where('category_id', $this->category_id)->count();
        if (!$this->ismeta) {
            $all_vpids = Viewpoint::where('category_id', $this->category_id)->where('forrev', 1)->count();
        }
        // ->whereNotIn('id', $finish_vpids)->
        if ($finish_vpids == 0) {
            $this->status = 0;
        } else if ($finish_vpids == $all_vpids) {
            $this->status = 2;
        } else {
            $this->status = 1;
        }
        $this->save();
    }

    /**
     * 未回答があると $rev->scores は抜けてしまうので、viewpoints をつかってKey->value として確実に配列で返す。
     * @param $only_score が 1のとき、number が含まれるものだけに限定する（通常はしないので0）
     * @param $accepted が 0のとき、doReturnAcceptOnly が 1のものは表示しない
     */
    public function scores_and_comments($only_doreturn = 1, $only_score = 0, $accepted = 1)
    {
        $aryscores = $this->scores->pluck("valuestr", "viewpoint_id")->toArray();
        $vps = Viewpoint::where('category_id', $this->category_id)->orderBy('orderint')->get();
        $ret = [];
        foreach ($vps as $vp) {
            if ($only_doreturn && !$vp->doReturn) continue;
            if ($only_score && strpos($vp->content, "number") === false) continue;
            // Primaryじゃないとき(ismeta=0)、forrev=0のときは表示しない
            if (!$this->ismeta && !$vp->forrev) continue;
            if (!$accepted && $vp->doReturnAcceptOnly) continue;

            $ret[$vp->desc] = (isset($aryscores[$vp->id])) ? $aryscores[$vp->id] : "(未入力)";
        }
        return $ret;
    }

    /**
     * txtに含まれるURLをリンクに変換する
     */
    public static function urllink($txt)
    {
        $txt = preg_replace_callback("/(<a [^>]+?>.+?<\/a>)|(https?:\/\/[a-zA-Z0-9_\.\/\~\%\:\#\?=&\;\-]+)/i", ["App\Models\Review","urllink_callback"], $txt);
        $txt = strip_tags($txt, "<a>");
        return $txt;
    }

    public static function urllink_callback($match)
    {
        if ($match[1]) {
            // 最初から<a>タグで囲まれている場合
            if (preg_match('/<a .*?href *?= *\"(http[^\"]+?)\"[^>]*?>(.+?)<\/a>/i', $match[1], $matches)) {
                //  <a>タグの href属性が http から始まっている場合（javascript対策）
                return sprintf(
                    '<a class="text-blue-600 hover:underline" href="%1$s" target="_blank">%2$s</a>',
                    htmlspecialchars($matches[1]),
                    htmlspecialchars($matches[2]),
                );
            } else {
                //  <a>タグの href属性が http から始まっていない場合はエスケープして出力
                return htmlspecialchars($match[1]);
            }
        } elseif ($match[2]) {
            // <a>タグで囲まれていないけど http://～ から始まっている場合
            return sprintf(
                '<a class="text-blue-600 hover:underline" href="%1$s" target="_blank">%1$s</a>',
                htmlspecialchars($match[2]),
            );
        }
    }

    /**
     * すべてのstatusを更新する（査読未完了のチェックの前に実行する）
     */
    public static function validateAllRev()
    {
        $all = Review::all();
        foreach ($all as $rev) {
            $rev->validateOneRev();
        }
    }

    /**
     * 自分が入力したスコア一覧 (indexcatの下に表示するmyscoresで使用)
     * @param int $uid
     * @param int $cat_id
     * 
     * @return array
     * $ret['titles'] = $titles;
     * $ret['scores'] = $scores;
     * $ret['descs'] = $descs;
     */
    public static function my_scores($uid, $cat_id)
    {
        // review list
        $sql1 =
            'select reviews.id, paper_id, title from reviews left join papers on reviews.paper_id = papers.id where reviews.user_id = ' .
            $uid .
            " and reviews.category_id = $cat_id order by paper_id";
        $res1 = DB::select($sql1);
        $titles = [];
        foreach ($res1 as $res) {
            $titles[$res->paper_id] = $res->title;
        }
        $sql2 =
            'select paper_id, viewpoint_id, value, orderint, `desc` from scores ' .
            ' left join reviews on scores.review_id = reviews.id' .
            ' left join viewpoints on scores.viewpoint_id = viewpoints.id' .
            ' where reviews.user_id = ' .
            auth()->id() .
            " and reviews.category_id = $cat_id " .
            ' and value is not null order by paper_id, orderint';
        $res2 = DB::select($sql2);
        $scores = [];
        $descs = [];
        foreach ($res2 as $res) {
            $scores[$res->paper_id][$res->viewpoint_id] = $res->value;
            $descs[$res->viewpoint_id] = $res->desc;
        }
        $ret['titles'] = $titles;
        $ret['scores'] = $scores;
        $ret['descs'] = $descs;
        return $ret;
    }

    /**
     * あるPaperIDに対して、査読者のスコアを取得する
     * @param int $paper_id
     * @param int $cat_id
     * 
     */
    public static function get_scores($paper_id, $cat_id)
    {
        $sql1 =
            'select reviews.id, paper_id, title, name, affil, ismeta, status from reviews ' .
            'left join papers on reviews.paper_id = papers.id ' .
            'left join users on reviews.user_id = users.id ' .
            'where reviews.paper_id = ' . $paper_id .
            " and reviews.category_id = $cat_id order by ismeta desc, id";
        $res1 = DB::select($sql1);
        $names = [];
        $ismeta = [];
        foreach ($res1 as $res) {
            $names[$res->id] = $res->name . " (" . $res->affil . ")";
            $ismeta[$res->id] = $res->ismeta;
        }
        $sql2 =
            'select scores.review_id, viewpoint_id, value, orderint, `desc` from scores ' .
            ' left join reviews on scores.review_id = reviews.id' .
            ' left join viewpoints on scores.viewpoint_id = viewpoints.id' .
            " where review_id in (select id from reviews where paper_id = {$paper_id}) " .
            " and reviews.category_id = $cat_id " .
            ' and value is not null order by review_id, orderint';
        $res2 = DB::select($sql2);
        $scores = [];
        $descs = [];
        foreach ($res2 as $res) {
            $scores[$res->review_id][$res->viewpoint_id] = $res->value;
            $descs[$res->viewpoint_id] = $res->desc;
        }
        $ret['names'] = $names;
        $ret['ismeta'] = $ismeta;
        $ret['scores'] = $scores;
        $ret['descs'] = $descs;
        return $ret;
    }
}
