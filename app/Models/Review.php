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
    public function submit()
    {
        return $this->belongsTo(Submit::class, 'submit_id');
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
     * 主にテスト用
     */
    public static function arr_up_status()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->user_id][$a->paper_id] = ($a->ismeta) ? 2 : 1;
        }
        return $ret;
    }
    /**
     * 主にテスト用
     */
    public static function arr_up_rev()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->user_id][$a->paper_id] = $a;
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
     * 査読者名を取得する
     * ret[paper_id][ismeta][user_id] = name
     */
    public static function arr_pu_revname()
    {
        $ret = [];
        foreach (Review::all() as $a) {
            $ret[$a->paper_id][$a->ismeta][$a->user_id] = $a->user->name;
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
        $finish_vpids_ary = Score::where('review_id', $this->id)->whereNotNull('valuestr')->whereHas('viewpoint', function ($query) {
            $query->where('mandatory', 1);
        })->get()->pluck('viewpoint_id')->toArray();
        $finish_vpids = count($finish_vpids_ary);
        // info("finish_vpids = {$finish_vpids}");
        // 自分が　ismeta なら、formetaの項目を数える。そうでなければ、forrev の項目を数える。
        if (!$this->ismeta) {
            $all_vpids = Viewpoint::where('category_id', $this->category_id)->where('forrev', 1)->where('mandatory', 1)->pluck('id')->toArray();
        } else {
            $all_vpids = Viewpoint::where('category_id', $this->category_id)->where('formeta', 1)->where('mandatory', 1)->pluck('id')->toArray();
        }
        // ->whereNotIn('id', $finish_vpids)->
        // info($all_vpids);
        if ($finish_vpids == 0) {
            $this->status = 0;
        } else if ($finish_vpids == count($all_vpids)) {
            // 厳密には、全ての必須項目が埋まっているかどうかをチェックするべき
            sort($finish_vpids_ary);
            sort($all_vpids);
            $answered = serialize($finish_vpids_ary);
            $expected = serialize($all_vpids);

            if ($answered == $expected) {
                $this->status = 2;
            } else {
                $this->status = 1;
            }
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
    public function scores_and_comments($only_doreturn = 1, $only_score = 0, $accepted = 1, $am_i_meta = 0)
    {
        $aryscores = $this->scores->pluck("valuestr", "viewpoint_id")->toArray();
        $vps = Viewpoint::where('category_id', $this->category_id)->orderBy('orderint')->get();
        $ret = [];
        foreach ($vps as $vp) {
            if ($only_doreturn && !$vp->doReturn) continue;
            if ($only_score && strpos($vp->content, "number") === false) continue;
            // Primaryじゃないとき(ismeta=0)、forrev=0のときは表示しない
            if (!$this->ismeta && !$vp->forrev) continue;
            if ($this->ismeta && !$vp->formeta) continue;
            if (!$accepted && $vp->doReturnAcceptOnly) continue;
            // hidefromrev = 1 のとき、一般査読者には見せない
            // if ($this->ismeta && $vp->hidefromrev && !$am_i_meta) continue;//$ret[$vp->desc] = 'hideen for rev';


            $ret[$vp->desc] = (isset($aryscores[$vp->id])) ? $aryscores[$vp->id] : "(未入力)";
            if ($this->ismeta && $vp->hidefromrev && !$am_i_meta) $ret[$vp->desc] = '(hidden for rev)';
        }
        return $ret;
    }

    /**
     * txtに含まれるURLをリンクに変換する
     */
    public static function urllink($txt)
    {
        $txt = preg_replace_callback("/(<a [^>]+?>.+?<\/a>)|(https?:\/\/[a-zA-Z0-9_\.\/\~\%\:\#\?=&\;\-]+)/i", ["App\Models\Review", "urllink_callback"], $txt);
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
            'select scores.review_id, viewpoint_id, value, orderint, viewpoints.formeta, viewpoints.forrev, viewpoints.`desc` from scores ' .
            ' left join reviews on scores.review_id = reviews.id' .
            ' left join viewpoints on scores.viewpoint_id = viewpoints.id' .
            " where review_id in (select id from reviews where paper_id = {$paper_id}) " .
            " and reviews.category_id = $cat_id " .
            ' and value is not null order by forrev desc, orderint'; // ここのforrev desc で、先にforrevを表示する。
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

    /**
     * インタラクティブ発表のための査読者割り当て（ランダム割り当て）
     * Random assign reviewers to papers
     * repnum: number of reviewers assigned to paper [regular, meta]
     */
    public static function randomAssign($repnum = [4, 1], $catids = [2, 3], $exclude = [])
    {
        info("random assignment task started");
        // 
        $revs[1] = Role::findByIdOrName("metareviewer")->users->shuffle()->pluck('affil', 'id')->toArray();
        $revs[0] = Role::findByIdOrName("reviewer")->users->shuffle()->pluck('affil', 'id')->toArray();
        foreach ($exclude as $ex => $uid) {
            unset($revs[0][$uid]);
            unset($revs[1][$uid]);
        }

        $papers = [];
        foreach ($catids as $catid) {
            $papers[$catid] = Paper::where("category_id", $catid)->get()->pluck('authorlist', 'id')->toArray();
        }

        $count_of_revs[0] = count($revs[0]);
        $count_of_revs[1] = count($revs[1]);
        $revids[0] = array_keys($revs[0]);
        $revids[1] = array_keys($revs[1]);

        $km = 0;
        $ret = [];
        $dupcheck = []; //paper_id to reviewer_id, for checking duplicate
        $pool_for_later = [1 => [], 0 => []]; // 所属チェックで問題があったものを後で再割り当てを試みる



        for ($ism = 0; $ism < 2; $ism++) { // ism = ismeta
            $km = 0;
            $num_of_assigned = [];
            for ($i = 0; $i < $repnum[$ism]; $i++) {
                foreach ($catids as $cat) {
                    // $copy_of_pool_for_later = $pool_for_later;
                    foreach ($papers[$cat] as $pid => $authers) {
                        if (!isset($ret[$pid]) || !is_array($ret[$pid])) $ret[$pid] = [];
                        $rid = -1;
                        if (count($pool_for_later[$ism]) > 0) {
                            // info($pool_for_later);
                            foreach ($pool_for_later[$ism] as $rrid) {
                                if (
                                    isset($dupcheck[$pid][$rrid])
                                    || Review::detectConflict($pid, $rrid)
                                ) {
                                } else { //repeat until no duplicate assignment
                                    $rid = $rrid;
                                    $key = array_search($rrid, $pool_for_later[$ism]);
                                    unset($pool_for_later[$ism][$key]);
                                    break;
                                }
                            }
                        }
                        if ($rid == -1) {
                            $rid = $revids[$ism][$km]; // reviewer id candidate
                            while (
                                isset($dupcheck[$pid][$rid])
                                || Review::detectConflict($pid, $rid)
                                || in_array($rid, $exclude)
                            ) { //repeat until no duplicate assignment
                                if (!in_array($rid, $exclude)) $pool_for_later[$ism][] = $rid; //次回以降、優先して割り当てる
                                $km = ($km + 1) % $count_of_revs[$ism];
                                $rid = $revids[$ism][$km];
                            }
                        }
                        $ret[$pid][] = ['uid' => $rid, 'affil' => $revs[$ism][$rid]];
                        Review::review_assign($pid, $rid, 1); // 一般査読者として登録
                        $num_of_assigned[$rid] = ($num_of_assigned[$rid] ?? 0) + 1;
                        $dupcheck[$pid][$rid] = $ism . " " . $i;
                        $km = ($km + 1);
                        if ($km >= $count_of_revs[$ism]) {
                            $km = 0;
                            // ここで一旦、割り当て数をチェックし、少ない人には優先poolにはいってもらう。
                            $max = max($num_of_assigned);
                            foreach ($revs[$ism] as $rrid => $affil) {
                                if (($num_of_assigned[$rrid] ?? 0) < $max) {
                                    if (!in_array($rrid, $exclude)) {
                                        $pool_for_later[$ism][] = $rrid;
                                        // info("added to pool_for_later: $rrid $affil"); // because {$num_of_assigned[$rrid]} < {$max}");
                                    }
                                }
                            }
                            shuffle($revids[$ism]);
                        }
                    }
                }
            }
        }
        info("random assignment task ended");
        return $ret; // not verified in terms of affils
    }
    /**
     * 自動査読割り当てにおける、所属のチェックと、別カテゴリでの利害申告状況の調査
     */
    public static function detectConflict(int $pid, int $rid)
    {
        $paper = Paper::find($pid);
        $rev = User::find($rid);
        $names_affils = $paper->authorlist_ary(); //ary[0]には氏名、ary[1]には所属がはいる
        foreach ($names_affils as $n => $a) {
            if ($a == $rev->affil) return true; // same affil
            if ($n == $rev->name) return true; // same namespace
        }
        // Paperのcontact に含まれていないか、調べる
        if ($paper->isCoAuthorEmail($rev->email)) return true;

        //本当は、別カテゴリでの利害申告状況を使いたいが、この段階ではまだ登壇の著者リストが完成していない。
        //そこで、該当査読者が申告した、登壇のPaperそれぞれについて、当該デモpaperとの共通著者を、contactから調べる。
        //一人でもかぶっていたら、回避する。
        $rigai_pids = RevConflict::rigaiPapersByUid($rid);
        $rigai_papers = Paper::whereIn('id', $rigai_pids)->get();
        foreach ($rigai_papers as $ripa) {
            if ($ripa->hasSharedContacts($pid)) return true;
        }
        return false;
    }
}
