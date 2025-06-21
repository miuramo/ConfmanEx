<?php

namespace App\Models;

use App\Mail\ForAuthor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'name',
        'category_id',
        'lastsent'
    ];

    /**
     * Role member追加から呼ばれる。
     */
    public static function bundleUser($uids, $sub, $body)
    {
        $targets = User::whereIn('id', $uids)->get();
        $mt = MailTemplate::create();
        $mt->from = "[:MAILFROM:]";
        $mt->to = "userid(" . implode(", ", $uids) . ")";
        $mt->subject = $sub;
        $mt->body = $body;
        $mt->lastsent = date("Y-m-d H:i:s");
        $mt->user_id = auth()->user()->id;
        $mt->save();
        if (isset($targets)) foreach ($targets as $target) {
            (new ForAuthor($target, $mt))->process_send();
        }
    }

    public function getreplacetxt(Paper|User $p_or_u)
    {
        if (strpos(get_class($p_or_u), "User") > 0) {
            $replacetxt["UID"] = $p_or_u->id;
            $replacetxt["NAME"] = $p_or_u->name;
            $replacetxt["AFFIL"] = $p_or_u->affil;
            $replacetxt["EMAIL"] = $p_or_u->email;
            $replacetxt["URL_FORGETPASS"] = route('password.request');
        } else {
            $replacetxt["PID"] = $p_or_u->id_03d();
            $replacetxt["TITLE"] = $p_or_u->title;
            $subid = $p_or_u->submits->first()->id;
            $sub = Submit::find($subid);
            $accid = $sub->accept_id;
            $replacetxt["ACCNAME"] = Accept::find($accid)->name;
            $replacetxt["BOOTH"] = $sub->booth ?? "(未設定)";
            $replacetxt["CATNAME"] = Category::find($p_or_u->category_id)->name;
            $replacetxt["OWNER"] = $p_or_u->paperowner->affil . " " . $p_or_u->paperowner->name . " 様";
            $replacetxt["AUTHORS"] = $p_or_u->bibauthors(true);
            $replacetxt["ABSTRACT"] = $p_or_u->abst;
            $replacetxt["BIBINFO_ERROR"] = $p_or_u->bibinfo_error();
        }
        $replacetxt["CONFTITLE"] = Setting::getval("CONFTITLE");
        $replacetxt["CONFURL"] = Setting::getval("CONF_URL");
        $replacetxt["APP_URL"] = env('APP_URL');
        $replacetxt["SYSTEM_URL"] = env('APP_URL');
        $replacetxt["URL"] = env('APP_URL');
        return $replacetxt;
    }
    public function make_subject(array $replacetxt): string
    {
        $sub = $this->subject;
        foreach ($replacetxt as $rpk => $rpv) {
            $sub = str_replace("[:" . $rpk . ":]", $rpv, $sub);
        }
        return $sub;
    }
    public function make_body(array $replacetxt): string
    {
        $body = $this->body;
        foreach ($replacetxt as $rpk => $rpv) {
            $body = str_replace("[:" . $rpk . ":]", $rpv, $body);
        }
        $body .= "\n\n-----\n[" . env("APP_NAME") . "](" . env("APP_URL") . ")";
        return $body;
    }
    public function handle_to()
    {
        // && や || を、セミコロンに変換する
        // $this->to = str_replace("&&", ";", $this->to);
        // $this->to = str_replace("||", ";", $this->to);
        // toをセミコロンまたは && または || で分割する
        $toary = preg_split('/\s*(&&|\|\||;)\s*/', $this->to);
        $toary = array_map("trim", $toary);
        $merged = []; // new Collection(); // Papersという名前だが、Users が混じる可能性もある。
        foreach ($toary as $one_to) {
            if (strlen($one_to) > 1) {
                eval("\$tmppapers = \App\Models\MailTemplate::mt_{$one_to} ;");
                if (isset($tmppapers)) $merged[] = $tmppapers;
                unset($tmppapers);
            }
        }
        $col = new Collection();
        foreach ($merged as $m) {
            if (is_array($m)) {
                foreach ($m as $n) $col->add($n);
            } else if (get_class($m) == 'Illuminate\Database\Eloquent\Collection') {
                $col = $col->merge($m);
            }
        }
        return $col;
    }
    public function first_item(): Paper|User|null
    {
        $papers = $this->handle_to();
        if (isset($papers) && isset($papers[0])) return $papers[0];
        else return null;
    }

    /**
     * 便宜上、papersにいれているが、中身はPaper or Userの配列
     */
    public function targets()
    {
        $papers = $this->handle_to();
        if (isset($papers)) return $papers;
        return null;
    }
    public function numpaper(): int
    {
        $papers = $this->handle_to();
        if (isset($papers)) return count($papers);
        return 0;
    }

    /**
     * 雛形のコピーを作成
     */
    public static function makecopy(int $mtid)
    {
        $mt = MailTemplate::find($mtid);
        $newmt = $mt->replicate(); // copy data
        $newmt->save();
        return $newmt;
    }

    /**
     * カテゴリの投稿すべて
     */
    public static function mt_category(int $cat)
    {
        return Paper::where('category_id', $cat)->get();
    }

    /**
     * 採択
     */
    public static function mt_accept(int ...$cats)
    {
        $papers = [];
        foreach ($cats as $cat) {
            $accept_ids = Accept::where('judge', '>', 0)->pluck("id")->toArray();
            $subs = Submit::where('category_id', $cat)->whereIn('accept_id', $accept_ids)->orderBy('paper_id')->get();
            foreach ($subs as $sub) $papers[] = $sub->paper;
        }
        return $papers;
    }
    public static function mt_reject(int ...$cats)
    {
        $papers = [];
        foreach ($cats as $cat) {
            $accept_ids = Accept::where('judge', '<', 0)->pluck("id")->toArray();
            $subs = Submit::where('category_id', $cat)->whereIn('accept_id', $accept_ids)->orderBy('paper_id')->get();
            foreach ($subs as $sub) $papers[] = $sub->paper;
        }
        return $papers;
    }
    public static function mt_paperid(...$args)
    {
        $papers = Paper::whereIn('id', $args)->get();
        return $papers;
    }
    /**
     * Accept tableのid
     */
    public static function mt_acc_id(...$accept_ids)
    {
        $papers = [];
        $subs = Submit::whereIn('accept_id', $accept_ids)->orderBy('paper_id')->get();
        foreach ($subs as $sub) $papers[] = $sub->paper;
        return $papers;
    }
    /**
     * Accept tableのjudge
     */
    public static function mt_acc_judge(...$accept_judges)
    {
        $papers = [];
        $accept_ids = Accept::whereIn('judge', $accept_judges)->pluck("id")->toArray();
        $subs = Submit::whereIn('accept_id', $accept_ids)->orderBy('paper_id')->get();
        foreach ($subs as $sub) $papers[] = $sub->paper;
        return $papers;
    }
    /**
     * 当初のcat_id and acc_id
     */
    public static function mt_cat_acc_id($catid, ...$accept_ids)
    {
        $papers = [];
        $subs = Submit::whereIn('accept_id', $accept_ids)->orderBy('paper_id')->get();
        foreach ($subs as $sub) {
            // 当初のcat_idは、submitのcat_idではなく、paperのcat_id
            if ($sub->paper->category_id == $catid) $papers[] = $sub->paper;
        }
        return $papers;
    }
    /**
     * ファイル無し投稿
     */
    public static function mt_nofile(...$args)
    {
        $papers = [];
        $cols = Paper::whereIn('category_id', $args)->get();
        foreach ($cols as $paper) {
            if ($paper->check_nofile()) $papers[] = $paper;
        }
        // $collection = $cols->reject(function ($paper, $key) {
        //     return !$paper->check_nofile();
        // });
        return $papers;
    }
    /**
     * タイトルなし投稿
     */
    public static function mt_notitle(...$args)
    {
        $papers = [];
        $cols = Paper::whereIn('category_id', $args)->get();
        foreach ($cols as $paper) {
            if ($paper->title == null || mb_strlen($paper->title) < 1) $papers[] = $paper;
        }
        return $papers;
    }
    /**
     * UserIDの羅列
     */
    public static function mt_userid(...$args)
    {
        $users = User::whereIn('id', $args)->get();
        return $users;
    }
    /**
     * RoleIDの羅列
     */
    public static function mt_roleid(...$args)
    {
        $users = [];
        $roles = Role::whereIn('id', $args)->get();
        foreach ($roles as $role) {
            foreach ($role->users as $u) {
                $users[] = $u;
            }
        }
        return $users;
    }
    /**
     * RoleIDの羅列
     */
    public static function mt_roleid_noaccess(...$args)
    {
        $users = [];
        $roles = Role::whereIn('id', $args)->get();
        foreach ($roles as $role) {
            foreach ($role->users as $u) {
                if ($u->last_access() == "---") {
                    $users[] = $u;
                }
            }
        }
        return $users;
    }
    /**
     * Bidding未完了がある査読者
     */
    public static function mt_miss_bid()
    {
        $missing = RevConflict::bidding_status(true); //skip_allfinished=true(すべて完了の人を除く)
        return User::whereIn('id', array_keys($missing))->get();
    }

    /**
     * 査読担当があるのに、まだ査読用ファイルをダウンロードしていないユーザ
     */
    public static function mt_notdownloaded($catid)
    {
        // 査読用ファイルをダウンロードしたユーザ
        // TODO: review_downzipのパラメータに、catidをいれる
        $downus = LogAccess::where('url', 'like', "/review_downzip/{$catid}%")->pluck("uid", "id")->toArray();
        // カテゴリ catid の査読担当者のうち、ダウンロードしたユーザ以外
        $notrevus = Review::where('category_id', $catid)->whereNotIn('user_id', $downus)->pluck("user_id", "id")->toArray();
        return User::whereIn('id', $notrevus)->get();
    }
    /**
     * 査読未完了
     */
    public static function mt_norev()
    {
        Review::validateAllRev(); // statusを更新
        $norev_userids = Review::select(["user_id"])
            ->whereNot("status", 2)
            ->groupBy("user_id")
            ->pluck("user_id")
            ->toArray();
        return User::whereIn('id', $norev_userids)->get();
    }
    /**
     * 査読未完了
     */
    public static function mt_norev_cat($catid)
    {
        Review::validateAllRev(); // statusを更新
        $norev_userids = Review::select(["user_id"])
            ->whereNot("status", 2)
            ->where('category_id', $catid)
            ->groupBy("user_id")
            ->pluck("user_id")
            ->toArray();
        return User::whereIn('id', $norev_userids)->get();
    }
    /**
     * 査読未完了
     */
    public static function mt_norev_catmeta($catid, $ismeta)
    {
        Review::validateAllRev(); // statusを更新
        $norev_userids = Review::select(["user_id"])
            ->whereNot("status", 2)
            ->where('category_id', $catid)
            ->where('ismeta', $ismeta)
            ->groupBy("user_id")
            ->pluck("user_id")
            ->toArray();
        return User::whereIn('id', $norev_userids)->get();
    }
    /**
     * 「条件付き採録のプライマリ査読者」のように、特定の採択ID（ジャッジ値ではない）の査読者
     */
    public static function mt_primary_of_acc(...$accids)
    {
        // submit→review->uid->user
        $subids = Submit::whereIn('accept_id', $accids)->pluck('id')->toArray();
        $revs = Review::whereIn('submit_id', $subids)->where('ismeta', 1)->get();
        $uids = $revs->pluck('user_id')->toArray();
        return User::whereIn('id', $uids)->get();
    }
    /**
     * 特定のPaperIDのプライマリ査読者
     */
    public static function mt_primary_of_paper(...$pids)
    {
        // submit→review->uid->user
        $subids = Submit::whereIn('paper_id', $pids)->pluck('id')->toArray();
        $revs = Review::whereIn('submit_id', $subids)->where('ismeta', 1)->get();
        $uids = $revs->pluck('user_id')->toArray();
        return User::whereIn('id', $uids)->get();
    }

    /**
     * 採択論文の著者と共著者（ユーザ単位での送信）
     */
    public static function mt_authors_accepted(...$catids)
    {
        $papers = [];
        // TODO
        // $subs = Submit::whereIn('category_id', $catids)->whereHas('accept', function ($query) {
        //     $query->where('judge', '>', 0);
        // })->get();
        // foreach ($subs as $sub) {
        //     $papers[] = $sub->paper;
        // }
        return $papers;
    }


    /**
     * 著者名未入力（採択分のみ）
     */
    public static function mt_noauthorlist($catid)
    {
        $papers = [];
        $accept_ids = Accept::where('judge', '>', 0)->pluck("id")->toArray();
        $subs = Submit::where('category_id', $catid)->whereIn('accept_id', $accept_ids)->get();
        // info($subs);
        foreach ($subs as $sub) {
            if (strlen($sub->paper->authorlist) < 3) {
                $papers[] = $sub->paper;
            }
        }
        return $papers;
    }
    /**
     * 書誌情報（和文アブスト、和文キーワード、英文Title）がない
     * 引数：catids
     * 採択分のみ、ということに注意。投稿時のチェックはPaper.validateBibinfo()で行う。
     */
    public static function mt_nobib(...$args)
    {
        // info($koumoku);
        $accPIDs = Submit::with('paper')->whereIn("category_id", $args)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->get()->pluck("paper_id")->toArray();

        $papers = [];
        // $cols = Paper::whereIn('category_id', $args)->whereNull("abst")->orWhereNull("keyword")->orWhereNull("etitle")->get();
        $koumoku = Paper::mandatory_bibs(); // 必要項目は、SKIP_BIBINFOにないもの
        $cols = Paper::whereIn('id', $accPIDs)
            ->where(function ($query) use ($koumoku) {
                foreach ($koumoku as $k => $v) {
                    $query->orWhereNull($k);
                }
            })->get();
        $error_ids = [];
        foreach ($cols as $paper) {
            $papers[] = $paper;
            $error_ids[] = $paper->id;
        }
        //エラーについても追加する
        $cols = Paper::whereIn('id', $accPIDs)->whereNotIn('id', $error_ids)->get();
        foreach ($cols as $paper) {
            if (count($paper->validateBibinfo()) > 0) {
                // info($paper->id." ".$paper->title);
                // info($paper->validateBibinfo());
                $papers[] = $paper;
            }
        }
        return $papers;
    }
    /**
     * 期日以前にアップされたPDFファイルのまま
     */
    public static function mt_oldfile($catid, $date)
    {
        $subs = Submit::subs_accepted($catid);
        $pid2sub = [];
        foreach ($subs as $sub) {
            $pid2sub[$sub->paper->id] = $sub;
        }
        $files = File::whereIn('paper_id', array_keys($pid2sub))
            ->where('valid', 1)->where('deleted', 0)
            ->where('created_at', '<', $date)->get()->sortByDesc('created_at');
        $papers = [];
        foreach ($files as $file) {
            $papers[] = Paper::find($file->paper_id);
        }
        return $papers;
    }

    /**
     * 公開予定のVIDEOファイルがある
     */
    public static function mt_hasvideo(...$catids)
    {
        $accPIDs = Submit::with('paper')->whereIn("category_id", $catids)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->get()->pluck("paper_id")->toArray();

        $papers = [];
        // $cols = Paper::whereIn('category_id', $args)->whereNull("abst")->orWhereNull("keyword")->orWhereNull("etitle")->get();
        $cols = Paper::with('video_file')->whereIn('id', $accPIDs)
            ->where(function ($query) {
                $query->whereNotNull("video_file_id");
            })->get();
        foreach ($cols as $paper) {
            $papers[] = $paper;
        }
        return $papers;
    }

    /**
     * 査読評価項目がN以上の論文
     * 論文誌への推薦 metasuisenjournal >= 2 の場合、
     * review_score(1, 'metasuisenjournal', '>=', 2) とする。
     * 注：現在は、1つでも条件にあうスコアがあれば、含まれる。平均値で絞り込む場合は、別途実装が必要。その場合は、$revid_scoreval と$revid_paperidを使って,paper毎の平均スコアを計算する。
     */
    public static function mt_review_score($catid, $name, $cop, $score)
    {
        // catidは、reviewを絞り込むため。

        $vp = Viewpoint::where("name", $name/*"metasuisenjournal"*/)->first();
        $revid_scoreval = Score::where('viewpoint_id', $vp->id)->where('value', $cop, $score)->pluck('value', 'review_id')->toArray();

        // まず、scoresから、該当するreview_idを取得する。
        $revid_paperid = Review::whereIn('id', array_keys($revid_scoreval))->where('category_id', $catid)->pluck('paper_id', 'id')->toArray();

        // そのpaper_idから、paperを取得する。
        $papers = Paper::whereIn('id', array_values($revid_paperid))->get();
        $array_papers = [];
        foreach ($papers as $paper) {
            $array_papers[] = $paper;
        }
        return $array_papers;
    }

    /**
     * catids のいずれかでアクセプトされ、まだnameのアンケートに回答していないPaper
     * （投稿時のカテゴリと、採択カテゴリが異なっている場合は、含まれないので、mt_noenqans_submitを使用する）
     */
    public static function mt_noenqans($name, ...$catids)
    {
        // 当初投稿時のcategory_idで絞り込む
        $target_paperids = Paper::whereIn('category_id', $catids)->whereNull('deleted_at')->pluck('id')->toArray();
        // $accPIDs = Submit::with('paper')->whereIn("category_id", $catids)->whereHas("accept", function ($query) {
        //         $query->where("judge", ">", 0);
        //     })->get()->pluck("paper_id")->toArray();
        $enqitm = EnqueteItem::where("name", $name)->first();
        $exist_enqansers_pid = EnqueteAnswer::where('enquete_item_id', $enqitm->id)->pluck('paper_id')->toArray();

        $noenqansPIDs = Submit::with('paper')->whereIn("category_id", $catids)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->whereNotIn('paper_id', $exist_enqansers_pid)->get()->pluck("paper_id")->toArray();

        $papers = Paper::whereIn('id', $noenqansPIDs)->whereIn("id", $target_paperids)->get();
        $array_papers = [];
        foreach ($papers as $paper) {
            $array_papers[] = $paper;
        }
        return $array_papers;
    }
    /**
     * catids のいずれかでアクセプトされ、まだnameのアンケートに回答していないPaper
     * （投稿時のカテゴリを指定）
     */
    public static function mt_noenqans_submit($name, ...$catids)
    {
        // 当初投稿時のcategory_idで絞り込む
        $target_paperids = Paper::whereIn('category_id', $catids)->whereNull('deleted_at')->pluck('id')->toArray();
        $enqitm = EnqueteItem::where("name", $name)->first();
        $exist_enqansers_pid = EnqueteAnswer::where('enquete_item_id', $enqitm->id)->pluck('paper_id')->toArray();
        $noenqansPIDs = Submit::with('paper')->whereIn("paper_id", $target_paperids)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->whereNotIn('paper_id', $exist_enqansers_pid)->get()->pluck("paper_id")->toArray();
        $papers = Paper::whereIn('id', $noenqansPIDs)->whereIn("id", $target_paperids)->get();
        $array_papers = [];
        foreach ($papers as $paper) {
            $array_papers[] = $paper;
        }
        return $array_papers;
    }

    /**
     * AltPDFのアンケート回答と、PDF提出の不一致
     */
    public static function mt_altpdf_inconsistent(array $catids, $enqname = "30sec_presen", $enqans_yes = "希望する")
    {
        // アンケート回答と、PDF提出を、それぞれ取得する。
        // まず、アンケート回答を取得
        $enqitm = EnqueteItem::where("name", $enqname)->first();
        $enqanswers_pid = EnqueteAnswer::where('enquete_item_id', $enqitm->id)->get()->where('valuestr', $enqans_yes)->pluck('paper_id')->toArray();

        //AltPDF提出
        $accPIDs = Submit::with('paper')->whereIn("category_id", $catids)->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->get()->pluck("paper_id")->toArray();
        $altpdf_pids = Paper::whereIn('id', $accPIDs)->whereNotNull('altpdf_file_id')->whereNull('deleted_at')->pluck('id')->toArray();

        // enqanswers_pid と、altpdf_pids の差分をとる
        $nofile = array_diff($enqanswers_pid, $altpdf_pids);
        $noenq = array_diff($altpdf_pids, $enqanswers_pid);
        $both = array_merge($nofile, $noenq);
        // return ["enq"=> $enqanswers_pid, "file"=>$altpdf_pids, "nofile"=>$nofile,"noenq"=>$noenq, "both"=>$both];

        $papers = Paper::whereIn('id', $both)->get();
        $array_papers = [];
        foreach ($papers as $paper) {
            $array_papers[] = $paper;
        }
        return $array_papers;
    }
}
