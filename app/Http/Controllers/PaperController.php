<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaperStoreRequest;
use App\Mail\Submitted;
use App\Models\Accept;
use App\Models\Category;
use App\Models\Confirm;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\Paper;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\User;
use App\Models\Viewpoint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaperController extends Controller
{
    /**
     * メール送信
     */
    public function sendSubmitted(string $id)
    {
        $aT = $this->author_check($id);
        if ($aT > 0) {
            $paper = Paper::with("contacts")->find($id);
            if ($paper->pdf_file_id != 0 && count($paper->validateFiles()) == 0) {
                // $paper->pendingMail("Submitted");
                (new Submitted($paper))->process_send();
                return redirect()->route('paper.edit', ['paper' => $paper->id])->with('feedback.success', "投稿状況メールを送信しました。");
            } else {
                return redirect()->route('paper.edit', ['paper' => $paper->id])->with('feedback.error', "投稿状況メールを送信しようとしましたが、まだ投稿が完了していませんでした。下のメッセージをご確認ください。");
            }
        } else {
            return redirect()->route('paper.index')->with('feedback.error', "権限がありません。");
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $all = Paper::where('owner', Auth::user()->id)->get()->sortBy("id");
        foreach ($all as $p) {
            $p->validate_accepted();
        }

        $coauthor_all = new Collection();
        $u = User::find(Auth::user()->id);
        if ($u != null) {
            $coauthor_all = $u->coauthor_papers();
        }

        return view("paper.index")->with(compact("all", "coauthor_all"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->name == User::$initialName) {
            return redirect()->route('user.profile.edit')->with('feedback.success', '最初に「氏 名」を設定してください。氏と名のあいだには半角スペースをいれてください。');
        }

        $kakunin = Confirm::where('grp', 1)->where('valid', 1)->select('name', 'mes')->get()->pluck('mes', 'name')->toArray();
        $mailkakunin = Confirm::where('grp', 2)->where('valid', 1)->select('name', 'mes')->get()->pluck('mes', 'name')->toArray();

        return view("paper.create")->with(compact("kakunin", "mailkakunin"));
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaperStoreRequest $request)
    {
        // バリデーションエラーが発生した場合
        return $request->shori();
    }
    /**
     * Update the specified resource in storage.
     * 投稿連絡用メールアドレスを更新
     */
    public function update(PaperStoreRequest $request, string $id)
    {
        return $request->shori_update($id);
    }

    /**
     * 他人にみられないように。共著者もOK
     */
    public function author_check(string $id): int
    {
        try {
            $paper = Paper::findOrFail($id);
            if (Gate::allows('show_paper', $paper)) {
                return $paper->getAuthorType();
            }
        } catch (ModelNotFoundException $ex) {
        }
        return -1;
    }
    /**
     * タイトル拡大画像
     *
     * TODO: URLを複雑にする
     */
    public function headimgshow(string $id, string $firsthash)
    {
        // PDFがあるか？複数あったらどうするか？
        // $aT = $this->author_check($id); // 所有確認
        // if ($aT < 0) return $this->noimage();

        // $paper = Paper::findOrFail($id);
        // if (!Gate::allows('show_paper', $paper)) {
        //     abort(403,'IMAGE FORBIDDEN');
        // }

        // $any = File::where('user_id', Auth::user()->id)->where('paper_id', $id)->where('mime', 'application/pdf')->first();
        $any = File::where('paper_id', $id)->where('mime', 'application/pdf')->where('key', 'like', $firsthash . "%")->first();
        if ($any != null) {
            // まだファイルがなければ、準備中をかえす
            if (!file_exists($any->getPdfHeadPath())) {
                $any->makePdfHeadThumb();
                $this->preparing_image();
                return;
            }
            return response()->file($any->getPdfHeadPath()); //->header('Content-Type: image/png');
            // } else {
            // }
        } else {
            return $this->noimage();
            // return;
        }
    }


    /**
     * ドロップ後の、Ajaxでの更新
     */
    public function filelist(string $id)
    {
        // $this->author_check($id); // 所有確認
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('show_paper', $paper)) {
            abort(403, 'forbidden_filelist');
        }
        // PDFがあるか？複数あったらどうするか？
        try {
            $all = File::where('user_id', Auth::user()->id)->where('paper_id', $id)->get()->sortByDesc("id");
            return view("paper.filelist", ["paper" => $id])->with(compact("id", "all"));
        } catch (ModelNotFoundException $ex) {
        }
    }
    private function noimage()
    {
        // ファイルがあれば、それを返す。
        $fn = "nofile.png";
        if (file_exists(storage_path(File::apf() . '/' . $fn))) {
            return response()->file(storage_path(File::apf() . '/' . $fn));
        }
        // ファイルがないので、作成する。
        $im = imagecreatetruecolor(300, 100);
        // $bg = imagecolorallocate($im, 153, 102, 255);
        $bg = imagecolorallocate($im, 255, 255, 230);
        imagefilledrectangle($im, 0, 0, 300, 100, $bg);
        imageAlphaBlending($im, true);
        imageSaveAlpha($im, true);
        $colw = imagecolorallocate($im, 255, 255, 255);
        $colb = imagecolorallocate($im, 100, 0, 0);
        $colr = imagecolorallocate($im, 205, 50, 50);
        $dejavu = public_path('font/DejaVuSans.ttf');

        for ($x = -2; $x < 3; $x++)
            for ($y = -2; $y < 3; $y++)
                ImageTTFText($im, 26, 0, 20 + $x, 47 + $y, $colr, $dejavu, "!!! Warning !!!");

        ImageTTFText($im, 26, 0, 20, 47, $colw, $dejavu, "!!! Warning !!!");
        // imagestring($im, 16, 20, 20, "!!! Warning !!!", $colw);
        // imagestring($im, 5, 20, 20, "!!! Warning !!!", $colw);

        for ($x = -2; $x < 3; $x++)
            for ($y = -2; $y < 3; $y++)
                ImageTTFText($im, 13, 0, 20 + $x, 80 + $y, $colb, $dejavu, "Paper PDF Not Uploaded Yet.");

        ImageTTFText($im, 13, 0, 20, 80, $colw, $dejavu, "Paper PDF Not Uploaded Yet.");

        // imagestring($im, 5, 20, 60, , $colw);
        // ob_start();
        // フォルダがなければ作る
        File::mkdir_ifnot(storage_path(File::apf()));

        imagepng($im, storage_path(File::apf() . '/' . $fn));
        imagedestroy($im);
        return response()->file(storage_path(File::apf() . '/' . $fn));

        // $img = ob_get_clean();
        // $size = strlen($img);
        // header("Content-Type: image/png");
        // header("Content-Length: {$size}");
        // echo $img;

    }
    // サムネイル準備中の画像
    private function preparing_image()
    {
        $im = imagecreate(300, 90);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $colw = imagecolorallocate($im, 255, 255, 255);
        $colc = imagecolorallocate($im, 102, 255, 255);

        for ($x = -2; $x < 3; $x++)
            for ($y = -2; $y < 3; $y++)
                imagestring($im, 5, 20 + $x, 40 + $y, "Preparing... Wait a moment.", $colc);

        imagestring($im, 5, 20, 40, "Preparing... Wait a moment.", $colw);
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // 権限チェックする 1=main 2=coauthor
        // $authorType = $this->author_check($id); // 所有確認
        try {
            $paper = Paper::findOrFail($id);
            if (!Gate::allows('show_paper', $paper)) {
                abort(403, 'forbidden_for_others');
            }
            $id_03d = sprintf("%03d", $id);

            // 回答可能(canedit)または参照可能(readonly)
            $enqs = Enquete::needForSubmit($paper);

            // 既存回答
            $eans = EnqueteAnswer::where('paper_id', $id)->get();
            $enqans = [];
            foreach ($eans as $ea) {
                $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
            }
            //ファイルエラー
            $fileerrors = $paper->validateFiles();

            return view("paper.show", ["paper" => $id])->with(compact("id", "id_03d", "paper", "enqs", "enqans", "fileerrors"));
        } catch (ModelNotFoundException $ex) {
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $paper = Paper::findOrFail($id);
            if (!Gate::allows('edit_paper', $paper)) {
                abort(403, 'forbidden_for_coauthor_or_others');
            }
            $id_03d = sprintf("%03d", $id);
            $all = File::where('user_id', Auth::user()->id)->where('paper_id', $id)->get()->sortByDesc("id");

            // 回答可能(canedit)または参照可能(readonly)
            $enqs = Enquete::needForSubmit($paper);
            $ids = array_keys($enqs['until']);
            // 既存回答
            $eans = EnqueteAnswer::where('paper_id', $id)->whereIn('enquete_id', $ids)->get();
            $enqans = [];
            foreach ($eans as $ea) {
                $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
            }

            //ファイルエラー
            $fileerrors = $paper->validateFiles();
            // アンケートエラー
            $enqerrors = Enquete::validateEnquetes($paper);

            $cat = Category::find($paper->category_id);
            // 書誌情報エラー(もしshow_bibinfo_btnが1かつ、書誌情報が無い場合)
            $biberrors = $paper->biberrors(); 
            $enqerrors = array_merge($enqerrors, $biberrors);

            // paper->validate_accepted()でもよいが、せっかくエラーを調べたので、それを使う。
            $paper->accepted = (count($fileerrors) == 0 && count($enqerrors) == 0);
            $paper->save();

            $koumoku = Paper::mandatory_bibs(); //必須書誌情報            

            return view("paper.edit", ["paper" => $id])->with(compact("id", "id_03d", "all", "paper", "enqs", "enqans", "fileerrors", "enqerrors", "biberrors", "cat", "koumoku"));
        } catch (ModelNotFoundException $ex) {
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $this->author_check($id); // 所有確認
        $paper = Paper::findOrFail($id);
        // $paper = Paper::where('owner', Auth::user()->id)->where('id', $id)->first();
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        // ロックされてたら、消せない。
        if ($paper->locked) {
            return redirect()->route('paper.index')->with('feedback.error', '削除失敗：投稿はロックされています');
        }
        foreach ($paper->files as $file) {
            $file->remove_the_file();
            $file->delete_me();
        }
        $paper->delete_me();
        return redirect()->route('paper.index')->with('feedback.success', '投稿情報と関連ファイルを削除しました');
    }

    public function review(string $id, string $token)
    {
        $paper = Paper::findOrFail($id);
        if (!auth()->user()->can('role_any', 'pc|reviewer|metareviewer')) {
            if (!Gate::allows('show_paper', $paper)) {
                abort(403, 'forbidden_for_others');
            }
        }
        if ($paper->token() != $token) return abort(403, "Review Browse TOKEN ERROR");
        $subs = $paper->submits;
        $accepts = Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        return view('paper.review', ['paper' => $id])->with(compact("subs", "paper", "accepts"));
    }

    public function paper_reviews_json(string $token)
    {
        $downloadkey = Setting::findByIdOrName("AWARDJSON_DLKEY", "value");
        if ($token != $downloadkey) abort(403);

        $accept_ids = Accept::where('judge', '>', 0)->pluck("id")->toArray();
        $subs = Submit::with("reviews")->whereIn('accept_id', $accept_ids)->orderBy('category_id')->orderBy('booth')->get();

        $ret = [];
        foreach ($subs as $sub) {
            $count = 0;
            $rret = [];
            $vpsubdescs = Viewpoint::where('category_id', $sub->category_id)
                ->select('subdesc', 'desc')
                ->get()
                ->pluck('subdesc', 'desc')
                ->toArray();

            foreach ($sub->reviews as $rev) {
                $count++;
                $rret[$count] = [];
                $rret[$count]['reviewer'] = '査読者' . $count;
                foreach ($rev->scores_and_comments(1, 0, 1) as $vpdesc => $valstr) {
                    $rret[$count][$vpdesc] = nl2br(htmlspecialchars($valstr));
                    if (isset($vpsubdescs[$vpdesc]))
                    $rret[$count][$vpdesc."_"] = $vpsubdescs[$vpdesc];
                }

            }
            $ret[$sub->booth] = $rret;
        }
        return json_encode($ret, JSON_THROW_ON_ERROR);
    }

    /**
     * PDFテキストをドラッグして選択、書誌情報(アブストラクトや英文タイトル)の設定
     */
    public function dragontext(string $id)
    {
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        // PDFがなければ終了
        if ($paper->pdf_file_id == null) {
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', 'PDFがありません。');
        }

        if ($paper->locked) {
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', '現在、投稿はロックされているため、書誌情報の設定はできません。');
        }

        $pdftext = $paper->pdf_file->getPdfText();
        // 書誌情報の設定項目
        $koumoku = Paper::mandatory_bibs();
        $koumokucolor = ['title' => 'teal', 'abst' => 'teal', 'keyword' => 'teal', 'authorlist' => 'teal', 'etitle' => 'lime', 'eabst' => 'lime', 'ekeyword' => 'lime', 'eauthorlist' => 'lime'];
        // $pdftext = mb_ereg_replace('\n+',"\n",$pdftext);
        $reps = ["ﬁ" => "fi", "ﬀ" => "ff", "ﬃ" => "ffi"];
        foreach ($reps as $riga => $non) {
            $pdftext = mb_ereg_replace($riga, $non, $pdftext);
        }
        return view('paper.dragontext', ['paper' => $id])->with(compact("pdftext", "paper", "koumoku", "koumokucolor"));
    }

    /**
     * title, abst, keyword, etitle, eabst, ekeyword 単体での更新
     */
    public function dragontextpost(Request $req, string $id)
    {
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        $target_field = $req->input("target_field");
        $target_value = $req->input("target_value");
        $maydirty = $req->input("maydirty");
        if (strlen($target_value) > 0) {
            $paper->{$target_field} = $target_value;
            // maydirty
            $md = $paper->maydirty;
            if ($maydirty == "true" || (isset($md[$target_field]) && $md[$target_field] == "true")) {
                $md[$target_field] = $maydirty;
                $paper->maydirty = $md;
            }
            $paper->save();
            return json_encode(["field" => $target_field, "value" => $target_value]);
        } else {
            return json_encode(["field" => $target_field, "value" => $paper->{$target_field}]);
        }
    }

    /**
     * 著者名と所属
     */
    public function update_authorlist(Request $req, string $id)
    {
        $paper = Paper::findOrFail($id);
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        $authorlist = $req->input("authorlist");
        $eauthorlist = $req->input("eauthorlist");
        if (strlen($authorlist) > 5 || strlen($eauthorlist) > 5) {
            $paper->authorlist = $authorlist;
            $paper->eauthorlist = $eauthorlist;
            $paper->save();
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.success', '著者名と所属を保存しました。');
        } else {
            return redirect()->route('paper.edit', ['paper' => $paper])->with('feedback.error', '著者名と所属を入力してください。');
        }
    }

    /**
     * 投稿Paperのロック状態管理 TODO:
     */
    public function adminlock(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);
        if ($req->method() === 'POST') {
            if (!str_starts_with($req->input('action'), "kobetsu")) { // action is lock or unlock, not "kobetsu"
                foreach ($req->all() as $k => $v) {
                    if (strpos($k, "targetcat") === 0) {
                        DB::transaction(function () use ($req, $v) {
                            $papers = Paper::where("category_id", $v)->get();
                            foreach ($papers as $paper) {
                                $paper->locked = ($req->input('action') === 'lock');
                                $paper->save();
                            }
                        });
                    }
                }
                return redirect()->route('paper.adminlock')->with('feedback.success', "選択カテゴリの投稿Paperを{$req->input('action')}にしました。（ただし、deleted is null が対象）");
            } else { // "kobetsu" case
                $pids = explode(",", $req->input('pid'));
                DB::transaction(function () use ($pids, $req) {
                    $papers = Paper::whereIn("id", $pids)->get();
                    foreach ($papers as $paper) {
                        $paper->locked = ($req->input('action') === 'kobetsu_lock');
                        $paper->save();
                    }
                });
                $mes = substr($req->input('action'), 8);
                return redirect()->route('paper.adminlock')->with('feedback.success', "指定されたPaperを{$mes}にしました。（ただし、deleted is null が対象）");
            }
        }

        $fs = ["valid", "locked"];
        $sql1 = "select count(id) as cnt, " . implode(",", $fs);
        $sql1 .= " ,category_id from papers where deleted_at is NULL group by " . implode(",", $fs);
        $sql1 .= " ,category_id order by category_id, " . implode(",", $fs);
        $cols = DB::select($sql1);

        $sql2 = "select id, " . implode(",", $fs);
        $sql2 .= " ,category_id from papers where deleted_at is NULL order by category_id, " . implode(",", $fs);
        $res2 = DB::select($sql2);
        $pids = [];
        foreach ($res2 as $res) {
            if (is_array(@$pids[$res->category_id][$res->valid][$res->locked])) {
                $pids[$res->category_id][$res->valid][$res->locked][] = sprintf("%03d", $res->id);
            } else {
                $pids[$res->category_id][$res->valid][$res->locked] = [];
                $pids[$res->category_id][$res->valid][$res->locked][] = sprintf("%03d", $res->id);
            }
        }
        return view('admin.paperlock')->with(compact("cols", "pids"));
    }

    /**
     * 出版が、ファイルを採用する（アップロードされたファイルに切り替える）
     * 関連：BbMesController.phpのadopt
     */
    public function fileadopt(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web')) abort(403);
        $paper = Paper::find($req->paper_id);
        if ($paper == null) {
            return redirect()->route('pub.paperfile', ['paper' => $req->paper_id])->with('feedback.error', 'Paperが見つかりません。');
        }
        if (!$req->file_id) {
            return redirect()->route('pub.paperfile', ['paper' => $req->paper_id])->with('feedback.error', 'ファイルがchkでチェックされていません。');
        }

        if ($req->action == "reject") {
            $file_id = $req->input("file_id");
            $rejectfile = \App\Models\File::find($file_id);
            $rejectfile->valid = 0;
            $rejectfile->deleted = 1;
            $rejectfile->pending = 0;
            $rejectfile->locked = 0;
            $rejectfile->save();
            return redirect()->route('pub.paperfile', ['paper' => $req->paper_id])->with('feedback.success', 'ファイルをInvalid & Deletedにしました。状況を再度、確認してください。');
        }

        $file = File::find($req->file_id);
        if ($req->ftype == 'pdf')
            $paper->pdf_file_id = $file->id;
        else if ($req->ftype == 'img')
            $paper->img_file_id = $file->id;
        else if ($req->ftype == 'video')
            $paper->video_file_id = $file->id;
        else if ($req->ftype == 'altpdf')
            $paper->altpdf_file_id = $file->id;
        else if ($req->ftype == 'pptx')
            $paper->pptx_file_id = $file->id;

        $paper->save();
        $file->pending = 0;
        $file->deleted = 0;
        $file->locked = 1;
        $file->save();

        return redirect()->route('pub.paperfile', ['paper' => $req->paper_id])->with('feedback.success', 'ファイルを採用しました。状況を再度、確認してください。');
    }
}
