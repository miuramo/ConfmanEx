<?php

namespace App\Models;

use App\Mail\ForAuthor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    use HasFactory;

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
            $replacetxt["NAME"] = $p_or_u->name;
            $replacetxt["AFFIL"] = $p_or_u->affil;
            $replacetxt["EMAIL"] = $p_or_u->email;
            $replacetxt["URL_FORGETPASS"] = route('password.request');
        } else {
            $replacetxt["PID"] = $p_or_u->id_03d();
            $replacetxt["TITLE"] = $p_or_u->title;
            $subid = $p_or_u->submits->first()->id;
            $accid = Submit::find($subid)->accept_id;
            $replacetxt["ACCNAME"] = Accept::find($accid)->name;
            $replacetxt["CATNAME"] = Category::find($p_or_u->category_id)->name;
        }
        $replacetxt["CONFTITLE"] = Setting::findByIdOrName("CONFTITLE", "value");
        $replacetxt["APP_URL"] = env('APP_URL');
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
        // toをセミコロンで分割する
        $toary = explode(";", $this->to);
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
     * カテゴリの投稿すべて
     */
    public static function mt_category(int $cat)
    {
        return Paper::where('category_id', $cat)->get();
    }

    /**
     * 採択
     */
    public static function mt_accept(int $cat)
    {
        $papers = [];
        $accept_ids = Accept::where('judge', '>', 0)->pluck("id")->toArray();
        $subs = Submit::where('category_id', $cat)->whereIn('accept_id', $accept_ids)->get();
        foreach ($subs as $sub) $papers[] = $sub->paper;
        return $papers;
    }
    public static function mt_reject(int $cat)
    {
        $papers = [];
        $accept_ids = Accept::where('judge', '<', 0)->pluck("id")->toArray();
        $subs = Submit::where('category_id', $cat)->whereIn('accept_id', $accept_ids)->get();
        foreach ($subs as $sub) $papers[] = $sub->paper;
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
        $subs = Submit::whereIn('accept_id', $accept_ids)->get();
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
        $subs = Submit::whereIn('accept_id', $accept_ids)->get();
        foreach ($subs as $sub) $papers[] = $sub->paper;
        return $papers;
    }
    /**
     * ファイル無し投稿
     */
    public static function mt_nofile(...$args)
    {
        $papers = [];
        $cols = Paper::whereIn('id', $args)->get();
        foreach ($cols as $paper) {
            if ($paper->check_nofile()) $papers[] = $paper;
        }
        // $collection = $cols->reject(function ($paper, $key) {
        //     return !$paper->check_nofile();
        // });
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
}
