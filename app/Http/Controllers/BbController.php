<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBbRequest;
use App\Http\Requests\UpdateBbRequest;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use Illuminate\Http\Request;

class BbController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        for ($i = 1; $i < 4; $i++) {
            $bbs[$i] = Bb::with("paper")->with("category")->where("type", $i)->get();
        }
        return view("bb.index")->with(compact("bbs"));
        //
    }

    public function index_for_pub()
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);

        $i = 3;
        $bbs[$i] = Bb::with("paper")->with("category")->with('last_message')->where("type", $i)->get();

        // 最終のメッセージ(bbmes)を含めて、掲示板(BB)を取得


        return view("bb.index_for_pub")->with(compact("bbs"));
        //
    }

    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);
        $catid = $req->input("catid");
        $type = $req->input("type");
        $pids = trim($req->input("pids"));
        if ($pids == "all") {
            $ary = MailTemplate::mt_category($catid); // return: array of paperobj
        } else if ($pids == "accepted") {
            $ary = MailTemplate::mt_accept($catid); // return: array of paperobj
        } else {
            $ary = Paper::whereIn('id', explode(",", $pids))->get();
        }
        foreach ($ary as $n => $paper) {
            Bb::make_bb($type, $paper->id, $paper->category_id);
        }
        // 出版担当からの作成のとき 1
        $for_pub = $req->input("for_pub");
        if ($for_pub) {
            return redirect()->route('bb.index_for_pub')->with('feedback.success', "作成しました。");
        }
        return redirect()->route('bb.index')->with('feedback.success', "作成しました。");
    }

    /**
     * Display the specified resource.
     */
    public function show(int $bbid, string $key)
    {
        $bb = Bb::with("messages")->with("paper")->with("category")->where('id', $bbid)->where('key', $key)->first();
        if ($bb == null) abort(403, 'bb not found');
        // type=1(査読掲示板) のとき、ユーザのrevid をセット
        if ($bb->type == 1) {
            $rev = Review::where("paper_id", $bb->paper_id)->where("category_id", $bb->category_id)->where("user_id", auth()->id())->first();
            if ($rev == null) $revid = null;
            else $revid = $rev->id;
            // 利害関係者は掲示板を見れないようにする
            $rigais = RevConflict::arr_pu_rigai($bb->category_id);
            if (isset($rigais[$bb->paper->id][auth()->id()]) && $rigais[$bb->paper->id][auth()->id()] < 3) {
                return abort(403, 'authors conflict');
            }
        } else {
            $revid = null;
        }
        return view("bb.show")->with(compact("bb", "revid"));
    }

    

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bb $bb)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        Bb::truncate();
        BbMes::truncate();
        return redirect()->route('bb.index')->with('feedback.success', "全削除しました。");
    }

    /**
     * 種別ごとに削除
     */
    public function destroy_bytype(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);
        $type = $req->input("type");
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) {
            if ($type != 3) abort(403);
        }
        $target_bbids = Bb::where("type", $type)->pluck("id");
        BbMes::whereIn("bb_id", $target_bbids)->delete();
        Bb::where("type", $type)->delete();
        $for_pub = $req->input("for_pub");
        if ($for_pub) {
            return redirect()->route('bb.index_for_pub')->with('feedback.success', "出版掲示板をすべて削除しました。");
        }
        return redirect()->route('bb.index')->with('feedback.success', "削除しました。");
    }

    public function multisubmit(Request $req, int $type = 3)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);
        $preface = "出版担当から著者の方に、個別の連絡事項があります。
以下の点についてご対応いただき、修正原稿を 掲示板 からアップロードしてください。";
        $subject = "出版担当からの連絡事項";
        $csv = '"=======
001
BodyLine1
BodyLine2
BodyLine3
======="
"=======
002
BodyLine1
BodyLine2
BodyLine3
======="
"=======
003
BodyLine1
BodyLine2
BodyLine3
======="';
        $typedesc = "出版掲示板"; // への一括書き込み
        if ($type == 1){
            $preface = str_replace("出版担当から著者の方に", "プログラム委員長から査読者の方に", $preface);
            $subject = str_replace("出版担当からの連絡事項", "プログラム委員長からの連絡事項", $subject);
            $preface = str_replace("いただき、修正原稿を 掲示板 からアップロードして", "", $preface);
            $typedesc = "査読者同士の事前議論掲示板";
        }
        if ($type == 2) {
            $preface = str_replace("出版担当から著者の方に", "メタ査読者から著者の方に", $preface);
            $subject = str_replace("出版担当からの連絡事項", "メタ査読者からの連絡事項", $subject);
            $typedesc = "メタ査読者と著者";
        }
        if ($req->has('action')) {
            $lines = explode("\r\n", $req->csv);
            $out = "";
            $buf = "";
            $subject = "";
            $pid = 0;
            $count = 0;
            $bufary = [];
            foreach ($lines as $n => $l) {
                $line = $l; // trim($l);
                if (preg_match("/={6,30}/", $line)) {
                    if ($pid == 0) {
                        continue;
                    }
                    $bufary[] = [
                        "PID" => $pid,
                        "subject" => trim($req->subject),
                        "body" => $req->preface . "\n" . $buf
                    ];
                    $buf = $subject = "";
                    $pid = 0;
                } elseif (preg_match("/^[0-9０-９]+$/", $line)) {
                    $pid = intval(mb_convert_kana($line, 'n', 'UTF-8'));
                    $count = 0;
                } else {
                    $buf .= $line . "\r\n";
                }
                $count++;
            }
            if ($req->input('action') == "submit") {
                foreach ($bufary as $n => $ba) {
                    Bb::submitplain(
                        $ba['PID'],
                        $req->input('type'),
                        $ba['subject'],
                        $ba['body']
                    );
                }
                return redirect()->route('bb.multisubmit', ['type' => $type])->with('feedback.success', "一括送信しました。")->with(compact("out", "bufary", "preface", "subject", "csv", "type","typedesc"));
            } else {
                $preface = $req->preface;
                $subject = $req->subject;
                $csv = $req->csv;
                return view('bb.multisubmit', ['type' => $type])->with(compact("out", "bufary", "preface", "subject", "csv", "type","typedesc"));
            }
        }
        return view('bb.multisubmit', ['type' => $type])->with(compact("type", "preface", "subject", "csv", "typedesc"));
    }

    

    /**
     * 未対応の掲示板フラグを変更
     */
    public function needreply(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);

        $needreply = $req->input("needreply");
        foreach ($req->input("bbids") as $n => $bbid) {
            $bb = Bb::find($bbid);
            $bb->needreply = $needreply;
            $bb->save();
        }
        return redirect()->route('bb.index_for_pub')->with('feedback.success', "フラグを変更しました。");
    }

    /** 
     * 投稿者や共著者以外のファイルについて、差し替え可能にするために paper_id をセットする 
     * PaperIDは、Bbのpaper_id を使う
    */
    public function set_file_paperid($fileid, $bbid)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403);
        $bb = Bb::find($bbid);
        if ($bb == null) abort(404, "bb not found");
        $file = \App\Models\File::find($fileid);
        if ($file == null) abort(404, "file not found");
        $file->paper_id = $bb->paper_id;
        $file->save();
        return redirect()->route('bb.show', ['bb' => $bb->id, 'key' => $bb->key])->with('feedback.success', "ファイルの paper_id をセットしました。");
        //        return view("bb.show")->with(compact("bb", "revid"));

    }
}
