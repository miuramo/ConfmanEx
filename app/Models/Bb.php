<?php

namespace App\Models;

use App\Mail\BbNotify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bb extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'paper_id',
        'category_id',
        'type',
        'key',
        'needreply',
        'isopen',
        'isclose',
        'subscribers',
    ];

    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function messages()
    {
        return $this->hasMany(BbMes::class, 'bb_id');
    }
    public function last_message()
    {
        return $this->hasOne(BbMes::class, 'bb_id')->latest();
    }

    public function nummessages()
    {
        // メッセージの数を返す

        return $this->hasMany(BbMes::class, 'bb_id')->count();
    }
    public static function make_bb(int $type, int $pid, int $cid)
    {
        $subs = [
            1 => "reviewer|metareviewer",
            2 => "pc|metareviewer|author",
            3 => "pub|author",
        ];
        $firstmes = [
            1 => "ここは査読者同士の事前議論掲示板です。\n査読者は自身を名乗らないでください。必要があればRevIDを用いてください。RevIDは送信フォームに表示されています。\n（RevIDが表示されていない場合は、査読を担当していません。）\n注：RevIDは査読者のIDではなく、査読割当てごとに異なるIDです。",
            2 => "ここはメタ査読者と著者の掲示板です。（プログラム委員長も閲覧できます。）",
            3 => "ここは出版担当と著者の掲示板です。",
        ];
        $nameofmeta = Setting::findByIdOrName('NAME_OF_META')->value;
        if ($nameofmeta != null) {
            $firstmes[2] = "ここは" . $nameofmeta . "と著者の掲示板です。（プログラム委員長も閲覧できます。）";
        }
        $bb = Bb::firstOrCreate([
            'paper_id' => $pid,
            'category_id' => $cid,
            'type' => $type,
        ], [
            'key' => Str::random(30),
            'subscribers' => $subs[$type],
            'needreply' => ($type == 3) ? 1 : 0,
        ]);
        $mes = BbMes::firstOrCreate([
            'bb_id' => $bb->id,
        ], [
            'user_id' => 0,
            'subject' => 'ごあんない',
            'mes' => $firstmes[$type],
        ]);
        return $bb; // Bb::with("messages")->with("paper")->with("category")->find($bb->id);
    }

    public static function submitplain(int $pid, int $type, string $subject, string $mes)
    {
        $paper = Paper::find($pid);
        if ($paper == null) return null;
        $bb = Bb::where("paper_id", $pid)->where("type", $type)->first();
        if ($bb == null) {
            $bb = Bb::make_bb($type, $pid, $paper->category_id);
        }
        $mes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => auth()->id(),
            'subject' => $subject,
            'mes' => $mes,
        ]);
        (new BbNotify($bb, $mes))->process_send();
        return $mes;
    }

    /**
     * Bb通知メールをおくる
     */
    public static function send_email_nofity(Bb $bb, BbMes $bbmes)
    {
        // pcのみ利害関係に注意する。
        (new BbNotify($bb, $bbmes))->process_send();
    }
    public function url()
    {
        return route('bb.show', ['bb' => $this->id, 'key' => $this->key]);
    }
    public static function url_from_rev(Review $rev, int $type = 1)
    {
        $bb = Bb::where("paper_id", $rev->paper_id)->where("category_id", $rev->category_id)->where("type", $type)->first();
        if ($bb == null) return null;
        return $bb->url();
    }
    public static function url_from_bbmesid(int $bbmesid)
    {
        $bbmes = BbMes::find($bbmesid);
        if ($bbmes == null) return null;
        return $bbmes->bb->url();
    }
    public static function url_from_bbid(int $bbid)
    {
        $bb = Bb::find($bbid);
        if ($bb == null) return null;
        return $bb->url();
    }

    public function get_mail_to_cc()
    {
        $tolist = [];
        $bcclist = [];
        $subary = explode("|", trim($this->subscribers));

        //利害関係配列
        $rigais = RevConflict::arr_pu_rigai($this->category_id);

        foreach ($subary as $role) {
            if ($role == "author") {
                $to_cc_list = $this->paper->get_mail_to_cc();
                $tolist[] = $to_cc_list['to'];
                $bcclist = array_merge($bcclist, $to_cc_list['cc']);
            } else if ($role == "pc" || $role == "pub") {
                $role = Role::findByIdOrName($role);
                foreach ($role->users as $u) {
                    if (isset($rigais[$this->paper_id][$u->id]) && $rigais[$this->paper_id][$u->id] < 3) continue; //利害or共著
                    // 出版掲示板ならば、利害関係者であっても送信してよいという考え方はある。ただ、通常はauthor(cc)で追加されるはずなので、ここで特別な処理をする必要はない。
                    if ($u->pivot->mailnotify == 0) continue; // mailnotifyが0のときは送信しない
                    $bcclist[] = $u->email;
                }
            } else if ($role == "metareviewer" || $role == "reviewer") {
                $revuids = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("ismeta", $role == "metareviewer")->pluck("user_id", "id")->toArray();
                $revus = User::whereIn("id", $revuids)->get();
                foreach ($revus as $u) {
                    if ($this->type == 1 && $role == "metareviewer") { //査読者同士の事前議論掲示板のときは、to:metaになる。 (メタと著者の掲示板のときは、to: author になるので、metaはbccに加わる。)
                        $tolist[] = $u->email;
                    } else {
                        $bcclist[] = $u->email;
                    }
                }
            }
        }
        // 保険のため、もしtolistが空だった場合は、個別に送信する
        if (count($tolist) == 0) {
            return ["separate_to" => $bcclist];
        }
        return ["to" => $tolist, "bcc" => $bcclist];
    }

    public function get_reviewers()
    {
        $revuids = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("ismeta", 0)->pluck("user_id", "id")->toArray();
        return User::whereIn("id", $revuids)->get();
    }
    public function revuid2rev()
    {
        $revuid2rev = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("ismeta", 0)->pluck("id", "user_id")->toArray();
        return $revuid2rev;
    }
    public function ismeta_myself()
    {
        // 自分がメタ査読者かどうかを返す
        $rev = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("user_id", auth()->id())->where("ismeta", 1)->first();
        return $rev != null;
    }
    public function metauser()
    {
        // メタ査読者を返す
        $rev = Review::where("paper_id", $this->paper_id)->where("category_id", $this->category_id)->where("ismeta", 1)->first();
        return $rev->user;
    }

    /**
     * ユーザIDから、シェファーディング掲示板を取得する
     */
    public static function getShepherdingBbs($user_id)
    {
        // get all meta reviews
        $metarev_pids = Review::where('user_id', $user_id)->where('ismeta', 1)->get()->pluck('paper_id')->toArray();
        return Bb::whereIn('paper_id', $metarev_pids)->where('type', 2)->get();
    }
}
